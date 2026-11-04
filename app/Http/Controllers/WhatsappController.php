<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\WhatsappClick;
use App\Support\WhatsApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WhatsappController extends Controller
{
    /**
     * Pages that link here (see components/whatsapp-link.blade.php). Anything
     * else is counted as "lainnya", so the admin statistics stay readable.
     */
    public const SOURCES = ['beranda', 'umroh', 'haji-khusus', 'paket', 'paket-umum', 'legalitas', 'tentang-kami', 'perwakilan', 'pembimbing', 'artikel', 'lainnya'];

    /**
     * Pre-filled messages for pages that are not about one departure.
     */
    private const MESSAGES = [
        'haji-khusus' => "Assalamu'alaikum, saya ingin bertanya tentang program haji khusus Amsa Nur.",
        'umroh' => "Assalamu'alaikum, saya ingin bertanya tentang jadwal umroh Amsa Nur.",
        'legalitas' => "Assalamu'alaikum, saya ingin menanyakan legalitas dan izin Amsa Nur.",
        'perwakilan' => "Assalamu'alaikum, saya ingin menanyakan perwakilan resmi Amsa Nur di daerah saya.",
    ];

    /**
     * Count the click (page and package only, no personal data), then send the
     * visitor to WhatsApp with a message that tells the team what they asked about.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        // Only plain strings: ?paket[]=x must not turn into a 500.
        $slug = $request->query('paket');
        $package = is_string($slug) && $slug !== ''
            ? Package::published()->where('slug', $slug)->first()
            : null;

        $number = WhatsApp::number($package?->whatsapp ?: config('site.whatsapp'));

        abort_if($number === '', 404);

        $source = $request->query('dari');
        $source = is_string($source) && in_array($source, self::SOURCES, true) ? $source : 'lainnya';

        if ($this->isCountable($request)) {
            WhatsappClick::create(['package_id' => $package?->getKey(), 'source' => $source]);
        }

        $message = $package?->whatsappMessage() ?? self::MESSAGES[$source] ?? config('site.whatsapp_message');

        return redirect()->away(WhatsApp::url($number, $message));
    }

    /**
     * Count real visits only: no HEAD requests, no bots or link previews, and
     * no hidden embeds (an <img> or fetch pointing here from another site).
     * Browsers that send no Sec-Fetch headers are still counted.
     */
    private function isCountable(Request $request): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        if (preg_match('/bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp|curl|wget|python|headless/i', (string) $request->userAgent())) {
            return false;
        }

        return in_array($request->header('Sec-Fetch-Mode'), [null, 'navigate'], true)
            && in_array($request->header('Sec-Fetch-Dest'), [null, 'document'], true);
    }
}
