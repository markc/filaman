<?php

namespace FilaMan\Pages\Http\Controllers;

use FilaMan\Pages\Models\Page;
use FilaMan\Pages\Services\PageCacheService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ApiController extends Controller
{
    protected PageCacheService $cacheService;

    public function __construct(PageCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    public function index(Request $request)
    {
        // Check cache first
        if ($cached = $this->cacheService->getCachedAllPages()) {
            $pages = $cached;
        } else {
            $pages = Page::getAllFromFiles();
            $this->cacheService->cacheAllPages($pages);
        }

        // Filter published pages for public API
        $pages = array_filter($pages, fn ($page) => $page->published);

        // Apply filters
        if ($category = $request->get('category')) {
            $pages = array_filter($pages, fn ($page) => $page->category === $category);
        }

        if ($search = $request->get('search')) {
            $search = strtolower($search);
            $pages = array_filter($pages, function ($page) use ($search) {
                $searchable = strtolower($page->title.' '.$page->description.' '.$page->content);

                return str_contains($searchable, $search);
            });
        }

        // Sort pages
        usort($pages, function ($a, $b) {
            if ($a->category !== $b->category) {
                return strcmp($a->category, $b->category);
            }

            return $a->order <=> $b->order;
        });

        // Transform for API response
        $apiPages = array_map(function ($page) {
            return [
                'slug' => $page->slug,
                'title' => $page->title,
                'description' => $page->description,
                'category' => $page->category,
                'order' => $page->order,
                'url' => "/pages/{$page->slug}",
                'api_url' => "/api/pages/{$page->slug}",
            ];
        }, $pages);

        return response()->json([
            'data' => array_values($apiPages),
            'meta' => [
                'total' => count($apiPages),
                'categories' => array_unique(array_column($apiPages, 'category')),
            ],
        ]);
    }

    public function show(string $slug)
    {
        // Check cache first
        if ($cached = $this->cacheService->getCachedPage($slug)) {
            $pageData = $cached;
        } else {
            $page = Page::createFromFile($slug);

            if (! $page || ! $page->published) {
                return response()->json(['message' => 'Page not found'], 404);
            }

            $pageData = [
                'slug' => $page->slug,
                'title' => $page->title,
                'description' => $page->description,
                'category' => $page->category,
                'order' => $page->order,
                'seo_title' => $page->seo_title,
                'seo_description' => $page->seo_description,
                'content' => $page->content,
                'content_html' => \Illuminate\Support\Str::markdown($page->content),
                'custom_css' => $page->custom_css,
                'custom_js' => $page->custom_js,
                'updated_at' => file_exists($page->getFilePath()) ? date('c', filemtime($page->getFilePath())) : null,
            ];

            $this->cacheService->cachePage($slug, $pageData);
        }

        return response()->json(['data' => $pageData]);
    }

    public function categories()
    {
        $pages = Page::getAllFromFiles();
        $pages = array_filter($pages, fn ($page) => $page->published);

        $categories = [];
        foreach ($pages as $page) {
            $category = $page->category;
            if (! isset($categories[$category])) {
                $categories[$category] = [
                    'name' => $category,
                    'count' => 0,
                    'pages' => [],
                ];
            }
            $categories[$category]['count']++;
            $categories[$category]['pages'][] = [
                'slug' => $page->slug,
                'title' => $page->title,
                'url' => "/pages/{$page->slug}",
            ];
        }

        return response()->json(['data' => array_values($categories)]);
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json(['message' => 'Search query must be at least 2 characters'], 400);
        }

        $pages = Page::getAllFromFiles();
        $pages = array_filter($pages, fn ($page) => $page->published);

        $results = [];
        $query = strtolower($query);

        foreach ($pages as $page) {
            $searchable = strtolower($page->title.' '.$page->description.' '.$page->content);
            if (str_contains($searchable, $query)) {
                $results[] = [
                    'slug' => $page->slug,
                    'title' => $page->title,
                    'description' => $page->description,
                    'category' => $page->category,
                    'url' => "/pages/{$page->slug}",
                    'snippet' => $this->getSearchSnippet($page->content, $query),
                ];
            }
        }

        return response()->json([
            'data' => $results,
            'meta' => [
                'query' => $request->get('q'),
                'total' => count($results),
            ],
        ]);
    }

    protected function getSearchSnippet(string $content, string $query, int $length = 150): string
    {
        $content = strip_tags(\Illuminate\Support\Str::markdown($content));
        $position = stripos($content, $query);

        if ($position === false) {
            return substr($content, 0, $length).(strlen($content) > $length ? '...' : '');
        }

        $start = max(0, $position - 50);
        $snippet = substr($content, $start, $length);

        if ($start > 0) {
            $snippet = '...'.$snippet;
        }

        if (strlen($content) > $start + $length) {
            $snippet .= '...';
        }

        return $snippet;
    }
}
