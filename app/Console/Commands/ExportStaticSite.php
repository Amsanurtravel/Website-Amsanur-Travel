<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Renders the public pages into plain files for a static host: the client
 * preview on Vercel, which cannot run PHP. Every page is crawled from the
 * home page and the sitemap. WhatsApp buttons point straight to wa.me (there
 * is no server to count clicks), filter links (?bulan=2026-12) become pages
 * of their own, and every internal link and file is checked. The result is
 * built next to the previous export and only replaces it when nothing is broken.
 */
class ExportStaticSite extends Command
{
    protected $signature = 'site:export
        {--url= : Alamat publik salinan statis (bawaan: SITE_PREVIEW_URL)}
        {--path=preview : Folder hasil, relatif terhadap folder proyek}';

    protected $description = 'Ekspor halaman publik menjadi file statis untuk pratinjau di Vercel';

    /** Marks a folder made by this command, so nothing else is ever replaced. */
    private const MARKER = '.site-export';

    /** Routes that answer with a file instead of a page. */
    private const FILE_ROUTES = ['/sitemap.xml', '/robots.txt'];

    private HttpKernel $kernel;

    private Filesystem $filesystem;

    private string $root;

    /** A path that does not exist, rendered as the 404 page. */
    private string $probe;

    /** @var array<string, string> request path and query => output file */
    private array $pages = [];

    /** @var list<string> */
    private array $queue = [];

    /** @var array<string, true> public files to copy, by URL path */
    private array $files = [];

    /** @var array<string, string|null> query of a /wa link => wa.me address */
    private array $whatsapp = [];

    /** @var list<string> */
    private array $errors = [];

    public function handle(HttpKernel $kernel, Filesystem $filesystem): int
    {
        $this->kernel = $kernel;
        $this->filesystem = $filesystem;

        // The same command object can run more than once in one process (tests, Artisan::call).
        $this->pages = $this->queue = $this->files = $this->whatsapp = $this->errors = [];

        $root = rtrim((string) ($this->option('url') ?: config('site.preview_url')), '/');
        $scheme = parse_url($root, PHP_URL_SCHEME);

        if (! filter_var($root, FILTER_VALIDATE_URL) || ! in_array($scheme, ['http', 'https'], true) || parse_url($root, PHP_URL_PATH) !== null) {
            $this->error("Alamat tidak valid: {$root}. Contoh: --url=https://websiteamsanurtravel.vercel.app");

            return self::FAILURE;
        }

        // A running Vite dev server would put localhost addresses in every page.
        if (is_file(public_path('hot'))) {
            $this->error('Server Vite (npm run dev) sedang berjalan. Hentikan dulu, lalu jalankan npm run build.');

            return self::FAILURE;
        }

        // The placeholder articles of a fresh local install (ArticleSeeder) must never reach the client.
        $samples = Article::published()->where('slug', 'like', 'contoh-%')->where('title', 'like', '[Contoh]%')->pluck('title');

        if ($samples->isNotEmpty()) {
            $this->error('Artikel contoh masih terbit: '.$samples->implode('; ').'. Jadikan draf dulu di admin (Artikel).');

            return self::FAILURE;
        }

        $target = $this->targetPath();

        if ($target === null) {
            return self::FAILURE;
        }

        $this->root = $root;
        $this->probe = '/halaman-tidak-ada-'.Str::lower(Str::random(8));

        config(['app.url' => $root, 'app.debug' => false]);
        URL::forceRootUrl($root);
        URL::forceScheme($scheme);

        $build = $target.'.partial';
        $filesystem->deleteDirectory($build);

        $this->enqueue('/', 'index.html');
        $this->enqueue('/sitemap.xml', 'sitemap.xml');
        $this->enqueue('/robots.txt', 'robots.txt');
        $this->enqueue($this->probe, '404.html');

        while (($uri = array_shift($this->queue)) !== null) {
            $this->exportPage($uri, $build);
        }

        $this->copyFiles($build);

        if ($this->errors !== []) {
            $filesystem->deleteDirectory($build);
            $this->error('Ekspor dibatalkan, folder lama tidak diubah:');

            foreach ($this->errors as $error) {
                $this->line("  - {$error}");
            }

            return self::FAILURE;
        }

        $filesystem->put($build.'/'.self::MARKER, "Dibuat oleh php artisan site:export untuk {$root}\n");

        if (! $this->replace($build, $target)) {
            return self::FAILURE;
        }

        $this->info(sprintf('%d halaman dan %d file siap di %s untuk %s.', count($this->pages), count($this->files), $target, $root));
        $this->line(sprintf('%d tautan WhatsApp mengarah langsung ke wa.me (klik tidak tercatat di salinan statis).', count($this->whatsapp)));

        return self::SUCCESS;
    }

    private function exportPage(string $uri, string $build): void
    {
        $response = $this->send($uri);
        $expected = $uri === $this->probe ? 404 : 200;

        if ($response->getStatusCode() !== $expected) {
            $this->errors[] = "{$uri} menjawab {$response->getStatusCode()}, seharusnya {$expected}";

            return;
        }

        $file = $build.'/'.$this->pages[$uri];
        $this->filesystem->ensureDirectoryExists(dirname($file));
        $this->filesystem->put($file, $this->rewrite((string) $response->getContent()));
    }

    private function send(string $uri): Response
    {
        // Identifies as a bot, so the WhatsApp redirect does not count these requests as clicks.
        $request = Request::create($this->root.$uri, 'GET', server: ['HTTP_USER_AGENT' => 'AmsaNurStaticExport/1.0 (bot)']);
        $response = $this->kernel->handle($request);
        $this->kernel->terminate($request, $response);

        return $response;
    }

    /**
     * Finds every address on this site that a page mentions (links, images,
     * structured data, the sitemap) and swaps the ones a static host cannot answer.
     */
    private function rewrite(string $content): string
    {
        $pattern = '#'.preg_quote($this->root, '#').'(?![\w.:-])[^\s"\'<>)\\\\]*#';

        return preg_replace_callback($pattern, function (array $match): string {
            // A full stop or comma after an address in running text is not part of it.
            $address = rtrim($match[0], '.,;:');

            return $this->resolve($address).substr($match[0], strlen($address));
        }, $content);
    }

    private function resolve(string $raw): string
    {
        $url = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5);
        $path = parse_url($url, PHP_URL_PATH) ?: '/';
        $query = (string) parse_url($url, PHP_URL_QUERY);
        $fragment = parse_url($url, PHP_URL_FRAGMENT);

        if ($path === '/wa') {
            if (! array_key_exists($query, $this->whatsapp)) {
                $this->whatsapp[$query] = $this->whatsappAddress($query);
            }

            return $this->whatsapp[$query] === null ? $raw : e($this->whatsapp[$query]);
        }

        if ($path === $this->probe) {
            return $raw;
        }

        if (in_array($path, self::FILE_ROUTES, true)) {
            return $raw;
        }

        if (pathinfo($path, PATHINFO_EXTENSION) !== '') {
            $this->files[$path] = true;

            return $raw;
        }

        if ($query === '') {
            $this->enqueue($path, $path === '/' ? 'index.html' : ltrim($path, '/').'.html');

            return $raw;
        }

        // A static host ignores the query string, so each filter gets a file of its own.
        $static = rtrim($path, '/').'/filter/'.$this->querySlug($query);
        $this->enqueue($path.'?'.$query, ltrim($static, '/').'.html');

        return e($this->root.$static.($fragment === null ? '' : '#'.$fragment));
    }

    private function whatsappAddress(string $query): ?string
    {
        $response = $this->send('/wa?'.$query);
        $location = (string) $response->headers->get('Location');

        if (! $response->isRedirect() || ! str_starts_with($location, 'https://wa.me/')) {
            $this->errors[] = "/wa?{$query} tidak mengarah ke wa.me";

            return null;
        }

        return $location;
    }

    /**
     * "bulan=2026-12&urut=harga" becomes "bulan-2026-12_urut-harga", whatever the order of the parameters.
     */
    private function querySlug(string $query): string
    {
        parse_str($query, $parameters);
        ksort($parameters);

        return collect($parameters)
            ->map(fn (mixed $value, string $key): string => Str::slug($key.' '.(is_string($value) ? $value : '')))
            ->implode('_');
    }

    private function enqueue(string $uri, string $file): void
    {
        // Two spellings of the same filter (parameters in another order) are one page.
        if (isset($this->pages[$uri]) || in_array($file, $this->pages, true)) {
            return;
        }

        $this->pages[$uri] = $file;
        $this->queue[] = $uri;
    }

    /**
     * Copies the images, styles and icons the pages use, plus anything a
     * stylesheet points to. Only files inside public/ (and the uploads behind
     * public/storage) are copied.
     */
    private function copyFiles(string $build): void
    {
        $allowed = array_filter([realpath(public_path()), realpath(storage_path('app/public'))]);
        $queue = array_keys($this->files);

        while (($path = array_shift($queue)) !== null) {
            $source = realpath(public_path(ltrim(rawurldecode($path), '/')));
            $inside = $source !== false && collect($allowed)->contains(fn (string $folder): bool => str_starts_with($source, $folder.DIRECTORY_SEPARATOR));

            if (! $inside || ! is_file($source)) {
                $this->errors[] = "File {$path} tidak ada di public/";

                continue;
            }

            $file = $build.rawurldecode($path);
            $this->filesystem->ensureDirectoryExists(dirname($file));
            $this->filesystem->copy($source, $file);

            if (str_ends_with($path, '.css')) {
                foreach ($this->stylesheetReferences($path, $this->filesystem->get($source)) as $reference) {
                    if (! isset($this->files[$reference])) {
                        $this->files[$reference] = true;
                        $queue[] = $reference;
                    }
                }
            }
        }
    }

    /**
     * @return list<string> URL paths of the fonts and images a stylesheet loads
     */
    private function stylesheetReferences(string $stylesheet, string $css): array
    {
        preg_match_all('#url\(\s*([\'"]?)([^\'")]+)\1\s*\)#i', $css, $matches);

        $references = [];

        foreach ($matches[2] as $reference) {
            $reference = trim($reference);

            if ($reference === '' || preg_match('#^(data:|[a-z][a-z0-9+.-]*://|//|\#)#i', $reference)) {
                continue;
            }

            $path = strtok($reference, '?#');
            $segments = [];

            foreach (explode('/', str_starts_with($path, '/') ? $path : dirname($stylesheet).'/'.$path) as $segment) {
                if ($segment === '..') {
                    array_pop($segments);
                } elseif ($segment !== '' && $segment !== '.') {
                    $segments[] = $segment;
                }
            }

            $references[] = '/'.implode('/', $segments);
        }

        return $references;
    }

    /**
     * The output folder. It may be new or an earlier export, never a folder of the project itself.
     */
    private function targetPath(): ?string
    {
        $path = rtrim((string) $this->option('path'), '/\\');
        $target = preg_match('#^([a-zA-Z]:)?[/\\\\]#', $path) ? $path : base_path($path);
        $project = realpath(base_path());
        $existing = realpath($target);

        if ($path === '' || ($existing !== false && str_starts_with($project.DIRECTORY_SEPARATOR, $existing.DIRECTORY_SEPARATOR))) {
            $this->error("Folder hasil tidak boleh folder proyek atau induknya: {$target}");

            return null;
        }

        if ($existing !== false && ! is_file($target.'/'.self::MARKER)) {
            $this->error("{$target} sudah ada dan bukan hasil site:export. Pilih folder lain dengan --path.");

            return null;
        }

        return $target;
    }

    /**
     * Puts the new export in place of the old one, keeping the old one if a program holds it open.
     */
    private function replace(string $build, string $target): bool
    {
        $old = $target.'.old';
        $this->filesystem->deleteDirectory($old);

        if ($this->filesystem->isDirectory($target) && ! $this->filesystem->moveDirectory($target, $old)) {
            $this->filesystem->deleteDirectory($build);
            $this->error("{$target} sedang dipakai program lain. Tutup programnya, lalu ulangi.");

            return false;
        }

        if (! $this->filesystem->moveDirectory($build, $target)) {
            $this->filesystem->moveDirectory($old, $target);
            $this->error("Hasil ekspor tidak bisa dipindah ke {$target}.");

            return false;
        }

        $this->filesystem->deleteDirectory($old);

        return true;
    }
}
