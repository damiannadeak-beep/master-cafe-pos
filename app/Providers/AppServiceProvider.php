<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Menu;
use App\Models\Bahan;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        \Illuminate\Database\Eloquent\Model::preventLazyLoading(!app()->isProduction());

        if ($this->app->bound('request')) {
            if ($this->app->environment('production') || request()->header('x-forwarded-proto') === 'https' || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || str_contains(request()->url(), 'https://')) {
                \Illuminate\Support\Facades\URL::forceScheme('https');
            }
        }

        View::composer('layouts.admin', function ($view) {
            $data = cache()->remember('admin_layout_stok_data', 15, function() {
                $menuMenipis = Menu::where('stok', '<', 10)->where('is_available', true)->get();
                $bahanMenipis = Bahan::where('stok', '<', 10)->get();
                $stokMenipisCount = $menuMenipis->count() + $bahanMenipis->count();
                $pendingReq = \App\Models\PermintaanBelanja::where('status', 'menunggu')->count();
                return compact('menuMenipis', 'bahanMenipis', 'stokMenipisCount', 'pendingReq');
            });

            $view->with($data);
        });

        View::composer('layouts.app', function ($view) {
            $isStoreOpen = \App\Models\KasirShift::where('status', 'open')->exists();
            $view->with(compact('isStoreOpen'));
        });
    }
}
