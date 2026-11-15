<?php

namespace App\Providers;

use App\View\Composers\LayoutComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Tampilan pagination bergaya SIPEMAL (tanpa Tailwind).
        Paginator::defaultView('pagination.sipemal');
        Paginator::defaultSimpleView('pagination.sipemal');

        // Data bersama untuk layout aplikasi (breadcrumb, notifikasi).
        View::composer('layouts.app', LayoutComposer::class);
    }
}
