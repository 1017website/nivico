<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function about()
    {
        return view('pages.about')->with('seoKey', 'about');
    }

    public function contact()
    {
        return view('pages.contact')->with('seoKey', 'contact');
    }
}
