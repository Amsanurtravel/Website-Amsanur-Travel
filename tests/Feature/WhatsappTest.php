<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\WhatsappClick;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsappTest extends TestCase
{
    use RefreshDatabase;

    private const BROWSER = 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 Chrome/130.0 Mobile Safari/537.36';

    public function test_click_on_a_package_is_counted_and_opens_whatsapp_with_the_package_message(): void
    {
        config(['site.whatsapp' => '0821-1222-0576']);
        $package = Package::factory()->create(['name' => 'Umroh Ekonomis 12 Hari', 'departure_date' => '2026-12-02']);

        $response = $this->get(route('whatsapp', ['dari' => 'paket', 'paket' => $package->slug]), ['User-Agent' => self::BROWSER]);

        $response->assertRedirect('https://wa.me/6282112220576?text='.rawurlencode("Assalamu'alaikum, saya ingin bertanya tentang paket Umroh Ekonomis 12 Hari keberangkatan 2 Desember 2026."));
        $this->assertDatabaseHas('whatsapp_clicks', ['package_id' => $package->id, 'source' => 'paket']);
        $this->assertStringNotContainsString('public', (string) $response->headers->get('Cache-Control'));
    }

    public function test_a_departure_can_use_its_own_whatsapp_number(): void
    {
        config(['site.whatsapp' => '0821-1222-0576']);
        $package = Package::factory()->create(['whatsapp' => '+62 812-5550-1234']);

        $this->get(route('whatsapp', ['paket' => $package->slug]), ['User-Agent' => self::BROWSER])
            ->assertRedirectContains('https://wa.me/6281255501234?text=');
    }

    public function test_page_clicks_use_the_page_message_and_a_clean_source(): void
    {
        config(['site.whatsapp' => '0821-1222-0576']);

        $this->get(route('whatsapp', ['dari' => 'haji-khusus']), ['User-Agent' => self::BROWSER])
            ->assertRedirectContains(rawurlencode('program haji khusus'));

        $this->get(route('whatsapp', ['dari' => '<script>Beranda</script>', 'paket' => 'tidak-ada']), ['User-Agent' => self::BROWSER])
            ->assertRedirectContains('https://wa.me/6282112220576?text=');

        // Unknown sources are grouped as 'lainnya' so the admin statistics stay readable.
        $this->assertSame(['haji-khusus', 'lainnya'], WhatsappClick::orderBy('id')->pluck('source')->all());
        $this->assertNull(WhatsappClick::latest('id')->first()->package_id);
    }

    public function test_bots_and_link_previews_are_not_counted(): void
    {
        config(['site.whatsapp' => '0821-1222-0576']);

        $this->get(route('whatsapp', ['dari' => 'beranda']), ['User-Agent' => 'WhatsApp/2.23.20.0 A'])->assertRedirect();
        $this->get(route('whatsapp', ['dari' => 'beranda']), ['User-Agent' => 'Googlebot/2.1'])->assertRedirect();

        $this->assertSame(0, WhatsappClick::count());
    }

    public function test_drafts_are_not_linked_and_missing_number_is_not_found(): void
    {
        config(['site.whatsapp' => null]);
        $draft = Package::factory()->draft()->create(['whatsapp' => '0812-0000-0000']);

        // A draft's own number is not used: drafts are not public yet.
        $this->get(route('whatsapp', ['paket' => $draft->slug]), ['User-Agent' => self::BROWSER])->assertNotFound();
        $this->assertSame(0, WhatsappClick::count());
    }
}
