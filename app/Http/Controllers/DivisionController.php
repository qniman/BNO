<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DivisionController extends Controller
{
    public function index(): View
    {
        return view('divisions.index', [
            'divisions' => config('bno.divisions'),
        ]);
    }
}
