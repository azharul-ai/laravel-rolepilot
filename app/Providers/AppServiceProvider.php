<?php

namespace App\Providers;

use App\Models\Menu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
    public function boot()
    {
        // Pass menus to the existing sidebar view
        View::composer('layouts.admin.sidebar', function ($view) {
            $user = Auth::user();

            if (! $user) {
                $view->with('menus', collect());

                return;
            }

            $canView = static fn (Menu $menu): bool => blank($menu->permission_name)
                || $user->can($menu->permission_name);

            $menus = Menu::query()
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->with([
                    'children' => fn ($query) => $query
                        ->where('is_active', true)
                        ->orderBy('order'),
                ])
                ->orderBy('order')
                ->get()
                ->filter(function (Menu $menu) use ($canView): bool {
                    $menu->setRelation(
                        'children',
                        $menu->children->filter($canView)->values()
                    );

                    return $canView($menu);
                })
                ->values();

            $view->with('menus', $menus);
        });
    }
}
