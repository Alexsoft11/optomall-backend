<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use BezhanSalleh\FilamentLanguageSwitch\Enums\Placement;


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
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['uz', 'ru', 'zh_CN'])
                ->visible(outsidePanels: true)
                ->outsidePanelRoutes([
                    'profile',
                    'home',
                ])
                ->outsidePanelPlacement(Placement::BottomRight)
                ->displayLocale('en')
                ->flags([
                    'uz' => asset('flags/uzbekistan.png'),
                    'ru' => asset('flags/russia.png'),
                    'zh_CN' => asset('flags/china.jpg'),
                ])
                ->flagsOnly()
                ->circular();
        });
    }
}
