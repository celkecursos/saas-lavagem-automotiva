<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Páginas institucionais estáticas (task-12, seção 3) — sem CMS,
 * conteúdo direto na view mesmo (baixo volume, sem necessidade de
 * admin gerenciar isso na v1).
 */
class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.about');
    }

    public function terms(): View
    {
        return view('pages.terms');
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    /**
     * XML dinâmico em vez de arquivo estático — usa url()/route() com o
     * APP_URL real, sem depender de trocar um domínio placeholder na
     * mão a cada deploy (task-12, seção 5).
     */
    public function sitemap(): Response
    {
        $urls = [
            route('home'),
            route('plans.index'),
            route('about'),
            route('terms'),
            route('privacy'),
            route('contact'),
        ];

        $xml = view('sitemap', compact('urls'))->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
