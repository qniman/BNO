<?php

namespace App\Http\Controllers\Registry;

use App\Events\ArchiveAccessLogged;
use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TerminalHomeRegistryController extends Controller
{
    public function edit(): View
    {
        Gate::authorize('manage-registry');

        $definitions = config('bno.home_terminal');
        $stored = SiteContent::query()->whereIn('key', array_keys($definitions))->pluck('body', 'key');

        $entries = [];
        foreach ($definitions as $key => $meta) {
            $entries[] = [
                'key' => $key,
                'label' => $meta['label'],
                'multiline' => $meta['multiline'] ?? false,
                'value' => $stored[$key] ?? $meta['default'],
            ];
        }

        return view('registry.terminal.edit', compact('entries'));
    }

    public function update(Request $request): RedirectResponse
    {
        Gate::authorize('manage-registry');

        $allowed = array_keys(config('bno.home_terminal'));

        $request->validate([
            'keys' => ['required', 'array'],
            'keys.*' => ['required', 'string', Rule::in($allowed)],
            'bodies' => ['required', 'array'],
            'bodies.*' => ['required', 'string', 'max:64000'],
        ]);

        if (count($request->keys) !== count($request->bodies)) {
            return back()->withErrors(['bodies' => 'Несовпадение числа полей.'])->withInput();
        }

        foreach ($request->keys as $i => $key) {
            SiteContent::query()->updateOrCreate(
                ['key' => $key],
                [
                    'label' => config('bno.home_terminal')[$key]['label'],
                    'body' => $request->bodies[$i],
                ]
            );
        }

        event(new ArchiveAccessLogged(
            userId: auth()->id(),
            action: 'registry_terminal_update',
            subjectType: SiteContent::class,
            subjectId: null,
            meta: ['keys' => $request->keys],
            ip: request()->ip(),
        ));

        return redirect()->route('registry.terminal.edit')->with('status', 'Терминал обновлён.');
    }
}
