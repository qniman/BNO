<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Incident;
use App\Models\SiteContent;
use App\Services\AnomalyPresentationService;
use App\Support\ArchiveAccess;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(AnomalyPresentationService $presentation): View
    {
        $threat = $presentation->threatSnapshot();

        $definitions = config('bno.home_terminal');
        $stored = SiteContent::query()
            ->whereIn('key', array_keys($definitions))
            ->pluck('body', 'key');

        $terminal = [];
        foreach ($definitions as $key => $meta) {
            $terminal[$key] = $stored[$key] ?? $meta['default'];
        }

        $warningBullets = $this->nonEmptyLines($terminal['home.warning_bullets']);
        $systemMessages = $this->nonEmptyLines($terminal['home.system_messages']);

        $c = ArchiveAccess::clearance();

        $incidents = Incident::query()
            ->where('clearance_level', '<=', $c)
            ->orderByDesc('occurred_on')
            ->limit(5)
            ->get();

        $articles = Document::query()
            ->where('type', 'статья')
            ->where('clearance_level', '<=', $c)
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();

        return view('home', [
            'threat' => $threat,
            'incidents' => $incidents,
            'terminal' => $terminal,
            'warningBullets' => $warningBullets,
            'systemMessages' => $systemMessages,
            'articles' => $articles,
            'effectiveClearance' => $c,
            'isDirector' => ArchiveAccess::isDirector(),
        ]);
    }

    /**
     * @return list<string>
     */
    private function nonEmptyLines(string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $text))));
    }
}
