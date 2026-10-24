<?php

namespace App\Providers;

use App\Models\PageType;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Pdo\Sqlite;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define('manage-types', fn (User $user): bool => $user->isAdmin());

        $this->registerUnicodeLike();

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(8)->by($request->ip());
        });

        View::composer('layouts.site', function (\Illuminate\View\View $view): void {
            $navTypes = Schema::hasTable('page_types')
                ? PageType::where('show_in_nav', true)->orderBy('sort')->orderBy('id')->get()
                : collect();

            $view->with('navTypes', $navTypes);
        });
    }

    /**
     * Встроенный LIKE в SQLite не учитывает регистр только для латиницы: «подъезд» не найдёт «Подъезд».
     * Подменяем его на версию с поддержкой Юникода.
     */
    private function registerUnicodeLike(): void
    {
        $this->app['events']->listen(ConnectionEstablished::class, function (ConnectionEstablished $event) {
            if ($event->connection->getDriverName() !== 'sqlite') {
                return;
            }

            $like = function ($pattern, $value, $escape = null) {
                if ($pattern === null || $value === null) {
                    return null;
                }
                $regex = '';
                $chars = mb_str_split((string) $pattern);
                for ($i = 0, $n = count($chars); $i < $n; $i++) {
                    $c = $chars[$i];
                    if ($escape !== null && $c === $escape && $i + 1 < $n) {
                        $regex .= preg_quote($chars[++$i], '/');
                    } elseif ($c === '%') {
                        $regex .= '.*';
                    } elseif ($c === '_') {
                        $regex .= '.';
                    } else {
                        $regex .= preg_quote($c, '/');
                    }
                }

                return preg_match('/^'.$regex.'$/usi', (string) $value) ? 1 : 0;
            };

            $pdo = $event->connection->getPdo();
            if ($pdo instanceof Sqlite) {
                $pdo->createFunction('like', $like, -1);
            } else {
                $pdo->sqliteCreateFunction('like', $like, -1);
            }
        });
    }
}
