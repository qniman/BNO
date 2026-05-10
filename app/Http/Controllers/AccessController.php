<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AccessController extends Controller
{
    public function index(): View
    {
        $sessionKey = 'access_state:'.request()->session()->getId();

        $state = Cache::remember($sessionKey, 600, function () {
            return random_int(0, 2);
        });

        return view('access.index', ['state' => $state]);
    }
}
