<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsService
{
    /**
     * Fetch all news items from the Go backend.
     *
     * Cached so the backend is not hit on every request. Returns null when
     * the backend is unreachable, so callers can render a friendly error
     * instead of a 500.
     *
     * @return array<int, array<string, mixed>>|null
     */
    public function all(): ?array
    {
        try {
            // return Cache::remember('news.all', config('news.cache_ttl'), function (): array {
                $response = Http::timeout(config('news.timeout'))
                    ->acceptJson()
                    ->get(config('news.api_url').'/news')
                    ->throw();

                return $response->json('data') ?? [];
            // });
        } catch (ConnectionException|RequestException $e) {
            Log::warning('News API unreachable', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Fetch a single news item by id.
     *
     * Returns the item array, null when not found, or false when the
     * backend is unreachable.
     *
     * @return array<string, mixed>|null|false
     */
    public function find(int $id): array|null|false
    {
        try {
            return Cache::remember("news.item.{$id}", config('news.cache_ttl'), function () use ($id): ?array {
                $response = Http::timeout(config('news.timeout'))
                    ->acceptJson()
                    ->get(config('news.api_url')."/news/{$id}");

                if ($response->notFound()) {
                    return null;
                }

                return $response->throw()->json('data');
            });
        } catch (ConnectionException|RequestException $e) {
            Log::warning('News API unreachable', ['id' => $id, 'error' => $e->getMessage()]);

            return false;
        }
    }
}
