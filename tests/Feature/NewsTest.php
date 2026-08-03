<?php

namespace Tests\Feature;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function fakeItem(int $id = 1): array
    {
        return [
            'id' => $id,
            'source' => 'Example News',
            'author' => 'Jane Doe',
            'title' => "Judul berita {$id}",
            'description' => 'Deskripsi singkat berita.',
            'url' => 'https://example.com/artikel',
            'url_to_image' => 'https://example.com/gambar.jpg',
            'published_at' => '2026-08-01T10:00:00Z',
            'content' => 'Isi lengkap berita.',
            'created_at' => '2026-08-01T10:00:00Z',
            'updated_at' => '2026-08-01T10:00:00Z',
        ];
    }

    public function test_index_shows_news_list(): void
    {
        Http::fake([
            '*/news' => Http::response(['source' => 'cache', 'data' => [$this->fakeItem()]]),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Judul berita 1')
            ->assertSee('Example News');
    }

    public function test_index_paginates_long_lists(): void
    {
        $items = collect(range(1, 30))->map(fn (int $i) => $this->fakeItem($i))->all();

        Http::fake([
            '*/news' => Http::response(['source' => 'cache', 'data' => $items]),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Judul berita 1')
            ->assertDontSee('Judul berita 30');

        $this->get('/?page=3')
            ->assertOk()
            ->assertSee('Judul berita 30');
    }

    public function test_index_returns_503_when_api_is_down(): void
    {
        Http::fake(fn () => throw new ConnectionException('Connection refused'));

        $this->get('/')
            ->assertServiceUnavailable()
            ->assertSee('tidak tersedia');
    }

    public function test_show_displays_a_single_item(): void
    {
        Http::fake([
            '*/news/1' => Http::response(['data' => $this->fakeItem()]),
        ]);

        $this->get('/berita/1')
            ->assertOk()
            ->assertSee('Judul berita 1')
            ->assertSee('https://example.com/artikel');
    }

    public function test_show_returns_404_for_missing_item(): void
    {
        Http::fake([
            '*/news/999' => Http::response(['error' => 'not found'], 404),
        ]);

        $this->get('/berita/999')->assertNotFound();
    }

    public function test_show_returns_503_when_api_is_down(): void
    {
        Http::fake(fn () => throw new ConnectionException('Connection refused'));

        $this->get('/berita/1')->assertServiceUnavailable();
    }

    public function test_news_list_is_cached(): void
    {
        Http::fake([
            '*/news' => Http::response(['source' => 'db', 'data' => [$this->fakeItem()]]),
        ]);

        $this->get('/')->assertOk();
        $this->get('/')->assertOk();

        Http::assertSentCount(1);
    }
}
