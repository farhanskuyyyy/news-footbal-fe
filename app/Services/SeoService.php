<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SeoService
{
    public const PAGES = ['landing', 'home', 'football.index', 'football.live', 'football.matchday', 'football.transfers', 'news.index'];

    public function url(string $path = ''): string
    {
        return rtrim(config('seo.url'), '/').'/'.ltrim($path, '/');
    }

    public function route(string $name, array $parameters = []): string
    {
        return $this->url(route($name, $parameters, false));
    }

    public function metadata(string $view, array $data, string $title): array
    {
        $route = request()->route()?->getName();
        $key = str_replace('.', '_', $route ?? 'landing');
        $description = __('seo.descriptions.'.$key);
        if (str_starts_with($description, 'seo.')) {
            $description = __('landing.description');
        }
        if (in_array($route, self::PAGES, true)) {
            $title = __('seo.titles.'.$key);
        }
        if ($route === 'news.index' && request()->integer('page') > 1) {
            $title .= ' · '.__('seo.page', ['number' => request()->integer('page')]);
        }

        $query = [];
        $allowed = match ($route) {
            'news.index' => ['page'],
            'football.index' => ['league_id', 'season_id', 'tab', 'round_id', 'type_id', 'team_id', 'status', 'sort'],
            'football.matchday' => ['date'],
            'football.team' => ['season_id'],
            default => [],
        };
        foreach ($allowed as $parameter) {
            $value = request()->query($parameter);
            if (is_string($value) && $value !== '' && ! ($parameter === 'page' && $value === '1')) {
                $query[$parameter] = $value;
            }
        }
        ksort($query);
        $canonical = $this->url(request()->path() === '/' ? '' : request()->path());
        if ($query !== []) {
            $canonical .= '?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        $indexable = in_array($route, [...self::PAGES, 'news.show', 'football.team', 'football.player', 'football.fixture'], true)
            && ! in_array($view, ['football.fixture_loading', 'football.unavailable', 'news.unavailable'], true);
        $image = null;
        $entity = null;
        if ($route === 'news.show' && isset($data['item'])) {
            $item = $data['item'];
            $description = $item['description'] ?? $description;
            $image = $this->httpUrl($item['url_to_image'] ?? null);
            if ($source = $this->httpUrl($item['url'] ?? null)) {
                $entity = ['@type' => 'NewsArticle', 'headline' => $item['title'] ?? $title, 'url' => $source];
                if (! empty($item['author'])) {
                    $entity['author'] = ['@type' => 'Person', 'name' => $item['author']];
                }
                if (! empty($item['source'])) {
                    $entity['publisher'] = ['@type' => 'Organization', 'name' => $item['source']];
                }
                if ($published = $this->date($item['published_at'] ?? null)) {
                    $entity['datePublished'] = $published;
                }
                if ($image) {
                    $entity['image'] = $image;
                }
            }
        }
        if (in_array($route, ['football.team', 'football.player'], true)) {
            $record = $data[$route === 'football.team' ? 'team' : 'player'] ?? [];
            $name = $record['display_name'] ?? $record['name'] ?? '';
            $description = __('seo.descriptions.'.$key, ['name' => $name]);
            $image = $this->httpUrl($record['image_path'] ?? null);
            if ($name !== '') {
                $entity = ['@type' => $route === 'football.team' ? 'SportsTeam' : 'Person', 'name' => $name, 'url' => $canonical];
                if ($route === 'football.team') {
                    $entity['sport'] = 'Football';
                }
                if ($image) {
                    $entity['image'] = $image;
                }
            }
        }
        if ($route === 'football.index' && ! empty($data['selectedLeague']['name'])) {
            $name = $data['selectedLeague']['name'];
            $title = __('seo.league_title', ['name' => $name]);
            $description = __('seo.league_description', ['name' => $name]);
        }
        if ($route === 'football.fixture' && ! empty($data['fixture']['name'])) {
            $name = $data['fixture']['name'];
            $description = __('seo.descriptions.football_fixture', ['name' => $name]);
            if ($start = $this->date($data['fixture']['starting_at'] ?? null)) {
                $entity = ['@type' => 'SportsEvent', 'name' => $name, 'sport' => 'Football', 'url' => $canonical, 'startDate' => $start];
                foreach (['home_team' => 'homeTeam', 'away_team' => 'awayTeam'] as $field => $property) {
                    if (! empty($data[$field]['name'])) {
                        $entity[$property] = ['@type' => 'SportsTeam', 'name' => $data[$field]['name']];
                    }
                }
                if (! empty($data['venue']['name'])) {
                    $entity['location'] = ['@type' => 'Place', 'name' => $data['venue']['name']];
                }
            }
        }

        $title = str_contains($title, 'PitchTicker') ? $title : $title.' · PitchTicker';
        $description = Str::limit(preg_replace('/\s+/u', ' ', strip_tags((string) $description)), 160);
        $page = ['@type' => 'WebPage', '@id' => $canonical.'#webpage', 'url' => $canonical, 'name' => $title, 'description' => $description, 'inLanguage' => app()->getLocale(), 'isPartOf' => ['@id' => $this->url().'#website']];
        if ($entity && $indexable) {
            $page[$route === 'news.show' ? 'about' : 'mainEntity'] = $entity;
        }
        $graph = [$page];
        if ($route === 'landing') {
            $graph[] = ['@type' => 'WebSite', '@id' => $this->url().'#website', 'name' => 'PitchTicker', 'url' => $this->url()];
        }

        return compact('title', 'description', 'canonical', 'indexable', 'image') + [
            'schema' => ['@context' => 'https://schema.org', '@graph' => $graph],
        ];
    }

    private function httpUrl(?string $value): ?string
    {
        return $value && filter_var($value, FILTER_VALIDATE_URL) && in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true) ? $value : null;
    }

    private function date(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        try {
            return Carbon::parse($value, 'UTC')->toIso8601String();
        } catch (\Exception) {
            return null;
        }
    }
}
