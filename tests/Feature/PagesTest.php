<?php

namespace Tests\Feature;

use App\Content\ContentRenderer;
use App\Content\PageWriter;
use App\Models\Page;
use App\Models\PageType;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_public_pages_render(): void
    {
        $this->get('/')->assertOk()->assertSee('Архив Бюро');
        $this->get('/obj')->assertOk()->assertSee('БНО-001');
        $this->get('/obj/bno-001')->assertOk()->assertSee('Зеркало в прихожей');
        $this->get('/info/about')->assertOk()->assertSee('БНО-002');
        $this->get('/tags')->assertOk();
        $this->get('/nope/nope')->assertNotFound();
    }

    public function test_redacted_text_never_reaches_html(): void
    {
        $html = (new ContentRenderer)->render([
            'type' => 'doc',
            'content' => [[
                'type' => 'paragraph',
                'content' => [['type' => 'text', 'text' => 'секретный город', 'marks' => [['type' => 'redacted']]]],
            ]],
        ]);

        $this->assertStringNotContainsString('секретный', $html);
        $this->assertStringContainsString('█', $html);
    }

    public function test_text_is_escaped_and_unsafe_links_dropped(): void
    {
        $html = (new ContentRenderer)->render([
            'type' => 'doc',
            'content' => [[
                'type' => 'paragraph',
                'content' => [['type' => 'text', 'text' => '<script>x</script>', 'marks' => [['type' => 'link', 'attrs' => ['href' => 'javascript:alert(1)']]]]],
            ]],
        ]);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function test_drafts_are_hidden_from_guests(): void
    {
        $type = PageType::where('key', 'article')->first();
        $draft = (new PageWriter)->save(new Page, $type, ['title' => 'Черновик', 'status' => 'draft'], null);

        $this->get($draft->url())->assertNotFound();
        $this->actingAs(User::first())->get($draft->url())->assertOk()->assertSee('Черновик');
    }

    public function test_search_is_case_insensitive_for_cyrillic(): void
    {
        $this->get('/search?q=подъезд')->assertOk()->assertSee('Подъезд, который не заканчивается');
    }

    public function test_admin_can_create_page_and_links_are_tracked(): void
    {
        $mirror = Page::where('number', 1)->first();

        $this->actingAs(User::first())
            ->post('/admin/pages/object', [
                'title' => 'Новый объект',
                'status' => 'published',
                'fields' => ['danger_class' => 'Опасный'],
                'content' => json_encode(['type' => 'doc', 'content' => [[
                    'type' => 'paragraph',
                    'content' => [['type' => 'text', 'text' => 'см. БНО-001', 'marks' => [['type' => 'pageLink', 'attrs' => ['pageId' => $mirror->id]]]]],
                ]]]),
            ])
            ->assertRedirect();

        $page = Page::where('title', 'Новый объект')->firstOrFail();
        $this->assertSame(3, $page->number);
        $this->assertSame('bno-003', $page->slug);
        $this->assertSame(1, $page->revisions()->count());

        $this->get($mirror->url())->assertSee('Новый объект');
    }

    public function test_guests_cannot_reach_admin(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }
}
