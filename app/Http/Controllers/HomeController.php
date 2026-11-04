<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Mentor;
use App\Models\Package;
use App\Models\Testimonial;
use App\Support\Faq;
use App\Support\StructuredData;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $faqs = Faq::home();

        return view('home', [
            'umrah' => Package::published()->upcoming()->ofType(Package::UMRAH)->with('mentor')->inDepartureOrder()->limit(3)->get(),
            'umrahCount' => Package::published()->upcoming()->ofType(Package::UMRAH)->count(),
            'haji' => Package::published()->upcoming()->ofType(Package::HAJI_KHUSUS)->inDepartureOrder()->first(),
            'mentors' => Mentor::active()->limit(2)->get(),
            'testimonials' => Testimonial::published()->limit(3)->get(),
            'articles' => Article::published()->with('category')->latest('published_at')->limit(3)->get(),
            'faqs' => $faqs,
            'schema' => [StructuredData::organization(), StructuredData::website(), StructuredData::faq($faqs)],
        ]);
    }
}
