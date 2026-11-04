<?php

namespace Tests\Feature;

use App\Filament\Widgets\WhatsappClicksOverview;
use App\Filament\Widgets\WhatsappTopSources;
use App\Models\Package;
use App\Models\User;
use App\Models\WhatsappClick;
use Filament\Auth\Pages\EditProfile;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        Filament::setCurrentPanel('admin');
    }

    private function signIn(): User
    {
        return tap(User::factory()->create(), fn (User $user) => $this->actingAs($user));
    }

    /**
     * @return array{0: Package, 1: Package, 2: Package}
     */
    private function seedClicks(): array
    {
        [$popular, $other, $old] = Package::factory()->count(3)->create();

        foreach (range(1, 3) as $i) {
            WhatsappClick::create(['package_id' => $popular->getKey(), 'source' => 'paket']);
        }
        WhatsappClick::create(['source' => 'beranda']);

        $this->travel(-10)->days(function () use ($other) {
            WhatsappClick::create(['package_id' => $other->getKey(), 'source' => 'paket']);
            WhatsappClick::create(['package_id' => $other->getKey(), 'source' => 'umroh']);
        });

        $this->travel(-40)->days(function () use ($old) {
            foreach (range(1, 5) as $i) {
                WhatsappClick::create(['package_id' => $old->getKey(), 'source' => 'paket']);
            }
        });

        return [$popular, $other, $old];
    }

    public function test_the_dashboard_and_login_page_carry_the_brand(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('images/amsa-nur-mark.png', false);

        $this->signIn();

        $this->get('/admin')->assertOk()->assertSee('Amsa Nur');
    }

    public function test_the_click_stats_count_the_last_7_and_30_days(): void
    {
        $this->signIn();
        [$popular] = $this->seedClicks();

        Livewire::test(WhatsappClicksOverview::class)
            ->assertSee('Klik WhatsApp')
            ->assertSeeInOrder(['7 hari terakhir', '4', '30 hari terakhir', '6', 'Paket paling diminati (30 hari)', $popular->name, '3 klik']);
    }

    public function test_the_click_stats_render_without_any_clicks(): void
    {
        $this->signIn();

        Livewire::test(WhatsappClicksOverview::class)
            ->assertSeeInOrder(['7 hari terakhir', '0', '30 hari terakhir', '0', 'Paket paling diminati (30 hari)', '-']);
    }

    public function test_the_top_sources_list_groups_by_page_and_package(): void
    {
        $this->signIn();
        [$popular, $other, $old] = $this->seedClicks();

        Livewire::test(WhatsappTopSources::class)
            ->assertSee('Asal klik WhatsApp')
            ->assertSeeInOrder([$popular->name, '3'])
            ->assertSee($other->name)
            ->assertSee('Beranda')
            ->assertSee('Umroh')
            ->assertDontSee($old->name);
    }

    public function test_the_top_sources_list_renders_when_empty(): void
    {
        $this->signIn();

        Livewire::test(WhatsappTopSources::class)->assertSee('Belum ada klik dalam 30 hari terakhir');
    }

    public function test_guests_cannot_open_the_profile_page(): void
    {
        $this->get('/admin/profile')->assertRedirect('/admin/login');
    }

    public function test_staff_can_change_their_name_on_the_profile_page(): void
    {
        $user = $this->signIn();

        $this->get('/admin/profile')->assertOk();

        Livewire::test(EditProfile::class)
            ->fillForm(['name' => 'Admin Amsa Nur'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Admin Amsa Nur', $user->fresh()->name);
    }
}
