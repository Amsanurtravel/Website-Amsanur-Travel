<?php

namespace App\Http\Controllers;

use App\Models\Mentor;
use App\Models\Representative;
use App\Support\Faq;
use App\Support\StructuredData;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function legal(): View
    {
        $faqs = Faq::legal();

        return view('pages.legal', [
            'faqs' => $faqs,
            'hasRepresentatives' => Representative::active()->exists(),
            'schema' => [
                StructuredData::organization(),
                StructuredData::faq($faqs),
                StructuredData::breadcrumbs([['Beranda', route('home')], ['Legalitas', route('legal')]]),
            ],
        ]);
    }

    public function privacy(): View
    {
        return view('pages.privacy', [
            'schema' => [StructuredData::breadcrumbs([['Beranda', route('home')], ['Kebijakan Privasi', route('privacy')]])],
        ]);
    }

    public function about(): View
    {
        return view('pages.about', [
            'mentors' => Mentor::active()->get(),
            'schema' => [
                StructuredData::organization(),
                StructuredData::breadcrumbs([['Beranda', route('home')], ['Tentang Kami', route('about')]]),
            ],
        ]);
    }

    public function mentors(): View
    {
        $mentors = Mentor::active()->get();

        abort_if($mentors->isEmpty(), 404);

        return view('pages.mentors', [
            'mentors' => $mentors,
            'schema' => [StructuredData::breadcrumbs([['Beranda', route('home')], ['Pembimbing Ibadah', route('mentors')]])],
        ]);
    }

    public function representatives(): View
    {
        return view('pages.representatives', [
            // Regions follow the sort order of their first representative.
            'regions' => Representative::active()->get()->groupBy('region'),
            'schema' => [StructuredData::breadcrumbs([['Beranda', route('home')], ['Perwakilan Daerah', route('representatives')]])],
        ]);
    }
}
