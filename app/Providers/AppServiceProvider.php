<?php

namespace App\Providers;

use App\Events\ArchiveAccessLogged;
use App\Listeners\PersistArchiveAccessLog;
use App\Repositories\Contracts\ObjectEntityRepositoryInterface;
use App\Repositories\EloquentObjectEntityRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ObjectEntityRepositoryInterface::class, EloquentObjectEntityRepository::class);
    }

    public function boot(): void
    {
        Event::listen(ArchiveAccessLogged::class, PersistArchiveAccessLog::class);

        Gate::define('manage-registry', function (): bool {
            return \App\Support\ArchiveAccess::canManageRegistry();
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(8)->by($request->ip());
        });

        View::composer('layouts.archive', function (\Illuminate\View\View $view): void {
            $pool = config('bno.archive_watch_messages', []);
            $hint = $pool !== [] ? $pool[array_rand($pool)] : null;
            $view->with('archiveWatchFooterHint', $hint);
        });
    }
}
