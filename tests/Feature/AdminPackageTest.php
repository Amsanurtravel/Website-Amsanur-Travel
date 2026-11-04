<?php

namespace Tests\Feature;

use App\Filament\Resources\Packages\PackageResource;
use App\Filament\Resources\Packages\Pages\CreatePackage;
use App\Filament\Resources\Packages\Pages\EditPackage;
use App\Filament\Resources\Packages\Pages\ListPackages;
use App\Models\Mentor;
use App\Models\Package;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPackageTest extends TestCase
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

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $package = Package::factory()->create();

        $this->get('/admin/packages')->assertRedirect('/admin/login');
        $this->get('/admin/packages/create')->assertRedirect('/admin/login');
        $this->get("/admin/packages/{$package->getKey()}/edit")->assertRedirect('/admin/login');
    }

    public function test_staff_can_open_the_package_pages(): void
    {
        $this->signIn();
        $umrah = Package::factory()->create(['mentor_id' => Mentor::factory()->create()->getKey()]);
        $haji = Package::factory()->hajiKhusus()->draft()->create();

        $this->get('/admin/packages')
            ->assertOk()
            ->assertSee($umrah->name)
            ->assertSee('Rp 35.000.000')
            ->assertSee('USD 16.500')
            ->assertSee('Mengikuti nomor porsi');
        $this->get('/admin/packages/create')->assertOk();
        $this->get("/admin/packages/{$umrah->getKey()}/edit")->assertOk();
        $this->get("/admin/packages/{$haji->getKey()}/edit")->assertOk();
    }

    public function test_the_list_shows_every_status_including_departed(): void
    {
        $this->signIn();
        $published = Package::factory()->create();
        $draft = Package::factory()->draft()->create();
        $scheduled = Package::factory()->create(['published_at' => now()->addWeek()]);
        $departed = Package::factory()->departed()->create(['seats_left' => 0]);

        Livewire::test(ListPackages::class)
            ->assertCanSeeTableRecords([$published, $draft, $scheduled, $departed])
            ->assertTableColumnFormattedStateSet('seats_left', 'Penuh', $departed)
            ->assertTableColumnFormattedStateSet('status', 'Terbit', $published)
            ->assertTableColumnFormattedStateSet('status', 'Draf', $draft)
            ->assertTableColumnFormattedStateSet('status', 'Terjadwal', $scheduled)
            ->assertTableColumnFormattedStateSet('status', 'Sudah berangkat', $departed)
            ->assertTableColumnStateSet('lowest_price', 'Rp 35.000.000', $published)
            ->assertActionVisible(TestAction::make('view')->table($published))
            ->assertActionHidden(TestAction::make('view')->table($draft));
    }

    public function test_the_list_can_be_filtered_by_type_status_and_departure(): void
    {
        $this->signIn();
        $upcoming = Package::factory()->create();
        $departed = Package::factory()->departed()->create();
        $draft = Package::factory()->draft()->create();
        $haji = Package::factory()->hajiKhusus()->create();

        Livewire::test(ListPackages::class)
            ->filterTable('type', Package::HAJI_KHUSUS)
            ->assertCanSeeTableRecords([$haji])
            ->assertCanNotSeeTableRecords([$upcoming, $departed, $draft]);

        Livewire::test(ListPackages::class)
            ->filterTable('status', 'draft')
            ->assertCanSeeTableRecords([$draft])
            ->assertCanNotSeeTableRecords([$upcoming, $departed, $haji]);

        Livewire::test(ListPackages::class)
            ->filterTable('departure', 'past')
            ->assertCanSeeTableRecords([$departed])
            ->assertCanNotSeeTableRecords([$upcoming, $draft, $haji]);

        Livewire::test(ListPackages::class)
            ->filterTable('departure', 'upcoming')
            ->assertCanSeeTableRecords([$upcoming, $draft, $haji])
            ->assertCanNotSeeTableRecords([$departed]);
    }

    public function test_staff_can_create_a_draft_umrah_package_with_a_generated_slug(): void
    {
        Repeater::fake();
        $this->signIn();

        $this->assertSame('2 Desember 2026', today()->setDate(2026, 12, 2)->translatedFormat('j F Y'));

        Livewire::test(CreatePackage::class)
            ->assertSchemaStateSet(['type' => Package::UMRAH, 'currency' => 'IDR'])
            ->fillForm([
                'name' => 'Umroh Ekonomis 12 Hari',
                'departure_date' => '2026-12-02',
                'duration_days' => 12,
                'price_quad' => 32_500_000,
                'price_double' => 36_000_000,
                'extra_costs' => [
                    ['label' => 'Perlengkapan dan asuransi', 'amount' => 1_750_000],
                    ['label' => 'Vaksin meningitis', 'amount' => null],
                ],
                'highlights' => [['item' => 'Umroh 3 kali']],
                'includes' => [['item' => 'Tiket pesawat pulang-pergi'], ['item' => 'Visa umroh']],
                'excludes' => [['item' => 'Paspor']],
                'requirements' => [['item' => 'Paspor berlaku minimal 8 bulan']],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $package = Package::sole();

        $this->assertSame('umroh-ekonomis-12-hari-2-desember-2026', $package->slug);
        $this->assertSame(Package::UMRAH, $package->type);
        $this->assertSame('IDR', $package->currency);
        $this->assertSame('draft', $package->status);
        $this->assertSame('2026-12-02', $package->departure_date->toDateString());
        $this->assertSame(32_500_000, $package->lowestPrice());
        $this->assertSame(['Umroh 3 kali'], $package->highlights);
        $this->assertSame(['Tiket pesawat pulang-pergi', 'Visa umroh'], $package->includes);
        $this->assertSame(['Paspor'], $package->excludes);
        $this->assertSame(['Paspor berlaku minimal 8 bulan'], $package->requirements);
        $this->assertEquals([
            ['label' => 'Perlengkapan dan asuransi', 'amount' => 1_750_000],
            ['label' => 'Vaksin meningitis', 'amount' => null],
        ], $package->extraCosts());
    }

    public function test_staff_can_create_a_haji_khusus_package_without_a_date(): void
    {
        $this->signIn();

        Livewire::test(CreatePackage::class)
            ->fillForm([
                'type' => Package::HAJI_KHUSUS,
                'name' => 'Haji Khusus Plus',
                'departure_note' => 'Mengikuti nomor porsi',
                'currency' => 'USD',
                'price_quad' => 16_500,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $package = Package::sole();

        $this->assertSame('haji-khusus-plus', $package->slug);
        $this->assertNull($package->departure_date);
        $this->assertSame('USD 16.500', $package->money($package->lowestPrice()));
    }

    public function test_editing_keeps_the_date_lists_and_slug_intact(): void
    {
        $this->signIn();
        $package = Package::factory()->create(['name' => 'Umroh Awal Ramadhan', 'departure_date' => '2027-02-10']);
        $slug = $package->slug;

        Livewire::test(EditPackage::class, ['record' => $package->getKey()])
            ->assertSchemaStateSet(['departure_date' => '2027-02-10'])
            ->fillForm(['seats_left' => 0])
            ->call('save')
            ->assertHasNoFormErrors();

        $package->refresh();

        $this->assertSame($slug, $package->slug);
        $this->assertSame('2027-02-10', $package->departure_date->toDateString());
        $this->assertSame(0, $package->seats_left);
        $this->assertSame(['Tiket pesawat kelas ekonomi pulang-pergi', 'Visa umroh'], $package->includes);
        $this->assertSame([['label' => 'Perlengkapan dan asuransi', 'amount' => 1_750_000]], $package->extra_costs);
    }

    public function test_type_and_name_are_required(): void
    {
        $this->signIn();

        Livewire::test(CreatePackage::class)
            ->fillForm(['type' => null, 'name' => null])
            ->call('create')
            ->assertHasFormErrors(['type' => 'required', 'name' => 'required']);
    }

    public function test_slug_must_be_unique_and_well_formed(): void
    {
        $this->signIn();
        Package::factory()->create(['slug' => 'sudah-ada']);

        Livewire::test(CreatePackage::class)
            ->fillForm(['name' => 'Baru', 'slug' => 'sudah-ada'])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);

        Livewire::test(CreatePackage::class)
            ->fillForm(['name' => 'Baru', 'slug' => 'Tidak Valid!'])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'regex']);
    }

    public function test_list_items_cannot_be_blank(): void
    {
        Repeater::fake();
        $this->signIn();

        Livewire::test(CreatePackage::class)
            ->fillForm([
                'name' => 'Baru',
                'includes' => [['item' => '']],
                'extra_costs' => [['label' => '', 'amount' => 100]],
            ])
            ->call('create')
            ->assertHasFormErrors(['includes.0.item' => 'required', 'extra_costs.0.label' => 'required']);
    }

    public function test_duplicating_creates_a_draft_with_a_new_date_and_slug(): void
    {
        $this->signIn();
        $original = Package::factory()->create([
            'name' => 'Umroh Reguler',
            'departure_date' => '2026-12-02',
            'seats_left' => 4,
        ]);

        $component = Livewire::test(ListPackages::class)
            ->callAction(TestAction::make('replicate')->table($original), data: ['departure_date' => '2027-01-10'])
            ->assertHasNoFormErrors();

        $copy = Package::whereKeyNot($original->getKey())->sole();

        $component->assertRedirect(PackageResource::getUrl('edit', ['record' => $copy]));

        $this->assertSame('umroh-reguler-10-januari-2027', $copy->slug);
        $this->assertSame('2027-01-10', $copy->departure_date->toDateString());
        $this->assertSame('draft', $copy->status);
        $this->assertSame($original->includes, $copy->includes);
        $this->assertSame($original->price_quad, $copy->price_quad);
        $this->assertSame('published', $original->fresh()->status);
        $this->assertSame('umroh-reguler-2-desember-2026', $original->fresh()->slug);
    }

    public function test_duplicating_starts_without_a_date_and_the_draft_slug_follows_the_new_date(): void
    {
        $this->signIn();
        $original = Package::factory()->create(['name' => 'Umroh Reguler', 'departure_date' => '2026-12-02']);

        Livewire::test(ListPackages::class)
            ->callAction(TestAction::make('replicate')->table($original));

        $copy = Package::whereKeyNot($original->getKey())->sole();

        // The copy never silently keeps the old date or SEO text.
        $this->assertNull($copy->departure_date);
        $this->assertSame('umroh-reguler', $copy->slug);
        $this->assertNull($copy->published_at);

        // While still a draft, giving it a date moves the address to that date.
        $copy->update(['departure_date' => '2027-01-10']);
        $this->assertSame('umroh-reguler-10-januari-2027', $copy->fresh()->slug);

        // Once published, the address is fixed.
        $copy->update(['published_at' => now()]);
        $copy->update(['departure_date' => '2027-01-17']);
        $this->assertSame('umroh-reguler-10-januari-2027', $copy->fresh()->slug);
    }
}
