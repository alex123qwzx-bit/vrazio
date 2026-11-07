<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function faq(): View
    {
        return view('pages.faq');
    }

    public function about(): View
    {
        return view('pages.about');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function shipping(): View
    {
        return view('legal.shipping');
    }

    public function returns(): View
    {
        return view('legal.returns');
    }

    public function warranty(): View
    {
        return view('legal.warranty');
    }

    public function privacy(): View
    {
        return view('legal.privacy');
    }

    public function terms(): View
    {
        return view('legal.terms');
    }
}
