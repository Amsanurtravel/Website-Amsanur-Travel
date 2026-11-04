<?php

namespace Tests\Feature;

use App\Filament\Resources\Mentors\Pages\CreateMentor;
use App\Filament\Resources\Mentors\Pages\ListMentors;
use App\Filament\Resources\Testimonials\Pages\CreateTestimonial;
use App\Filament\Resources\Testimonials\Pages\ListTestimonials;
use App\Models\Mentor;
use App\Models\Package;
use App\Models\Testimonial;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminMentorTestimonialTest extends TestCase
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
        $this->get('/admin/mentors')->assertRedirect('/admin/login');
        $this->get('/admin/testimonials')->assertRedirect('/admin/login');
    }

    public function test_staff_can_open_the_mentor_and_testimonial_pages(): void
    {
        $this->signIn();
        $mentor = Mentor::factory()->create();
        $testimonial = Testimonial::factory()->create();

        $this->get('/admin/mentors')->assertOk()->assertSee($mentor->name);
        $this->get('/admin/mentors/create')->assertOk();
        $this->get("/admin/mentors/{$mentor->getKey()}/edit")->assertOk();
        $this->get('/admin/testimonials')->assertOk()->assertSee($testimonial->name);
        $this->get('/admin/testimonials/create')->assertOk();
        $this->get("/admin/testimonials/{$testimonial->getKey()}/edit")->assertOk();
    }

    public function test_the_mentor_list_counts_packages(): void
    {
        $this->signIn();
        $mentor = Mentor::factory()->create();
        Package::factory()->count(2)->create(['mentor_id' => $mentor->getKey()]);

        Livewire::test(ListMentors::class)
            ->assertCanSeeTableRecords([$mentor])
            ->assertTableColumnStateSet('packages_count', 2, $mentor);
    }

    public function test_staff_can_add_a_mentor_with_a_photo(): void
    {
        Storage::fake('public');
        $this->signIn();

        Livewire::test(CreateMentor::class)
            ->assertSchemaStateSet(['role' => 'Pembimbing Ibadah', 'sort' => 0, 'is_active' => true])
            ->fillForm([
                'name' => 'KH. Ahmad Fulan, Lc.',
                'affiliation' => 'Pondok Pesantren Al-Amin',
                'photo_path' => UploadedFile::fake()->image('foto.jpg', 600, 750),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $mentor = Mentor::sole();

        $this->assertSame('Pembimbing Ibadah', $mentor->role);
        $this->assertTrue($mentor->is_active);
        Storage::disk('public')->assertExists($mentor->photo_path);
        $this->assertStringStartsWith('mentors/', $mentor->photo_path);
        $this->assertSame(600, $mentor->photo_width);
        $this->assertSame(750, $mentor->photo_height);
    }

    public function test_mentor_name_is_required(): void
    {
        $this->signIn();

        Livewire::test(CreateMentor::class)
            ->fillForm(['name' => null])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }

    public function test_new_testimonials_are_unpublished_until_switched_on(): void
    {
        $this->signIn();

        Livewire::test(CreateTestimonial::class)
            ->fillForm([
                'name' => 'Ibu Siti',
                'city' => 'Bekasi',
                'trip' => 'Umroh Desember 2025',
                'quote' => 'Pembimbingnya sabar dan hotelnya dekat.',
                'rating' => 5,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $testimonial = Testimonial::sole();

        $this->assertFalse($testimonial->is_published);
        $this->assertSame(5, $testimonial->rating);
        $this->assertSame(0, $testimonial->sort);
    }

    public function test_testimonial_name_and_quote_are_required_and_limited(): void
    {
        $this->signIn();

        Livewire::test(CreateTestimonial::class)
            ->fillForm(['name' => null, 'quote' => null])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required', 'quote' => 'required']);

        Livewire::test(CreateTestimonial::class)
            ->fillForm(['name' => 'Bapak Ahmad', 'quote' => str_repeat('a', 601)])
            ->call('create')
            ->assertHasFormErrors(['quote' => 'max']);
    }

    public function test_the_testimonial_list_shows_drafts_and_published(): void
    {
        $this->signIn();
        $testimonials = collect([
            Testimonial::factory()->create(),
            Testimonial::factory()->create(['is_published' => false]),
        ]);

        Livewire::test(ListTestimonials::class)->assertCanSeeTableRecords($testimonials);
    }
}
