<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use App\Support\ArchiveAccess;
use Illuminate\View\View;

class PersonnelController extends Controller
{
    public function index(): View
    {
        $staff = Personnel::query()
            ->where('clearance_level', '<=', ArchiveAccess::clearance())
            ->orderBy('division')
            ->orderBy('codename')
            ->paginate(30)
            ->withQueryString();

        return view('personnel.index', compact('staff'));
    }

    public function show(string $codename): View
    {
        $person = Personnel::query()->where('codename', $codename)->firstOrFail();

        $this->authorize('view', $person);

        return view('personnel.show', compact('person'));
    }
}
