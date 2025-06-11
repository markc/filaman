<?php

namespace FilaMan\PagesPlugin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\LaravelMarkdown\MarkdownRenderer;
use Spatie\YamlFrontMatter\YamlFrontMatter;

class PageController extends Controller
{
    public function show(Request $request, $slug = 'home')
    {
        // Use the helper to locate files within this plugin's resource path
        $filePath = filaman_plugin_path('pages', 'resources/views/pages/'.$slug.'.md');

        if (! File::exists($filePath)) {
            abort(404, "Page '{$slug}' not found.");
        }

        $content = File::get($filePath);
        $document = YamlFrontMatter::parse($content);

        $frontMatter = $document->matter();
        $markdownContent = $document->body();

        // Check if page is published
        if (! ($frontMatter['published'] ?? true)) {
            abort(404, "Page '{$slug}' is not published.");
        }

        // Render Markdown to HTML
        $htmlContent = app(MarkdownRenderer::class)->toHtml($markdownContent);

        // Prepare view data
        $viewData = [
            'title' => $frontMatter['title'] ?? Str::title(str_replace('-', ' ', $slug)),
            'description' => $frontMatter['description'] ?? '',
            'content' => $htmlContent,
            'slug' => $slug,
            'frontMatter' => $frontMatter,
            'pages' => filaman_get_pages(), // For navigation
        ];

        return view('filaman-pages::page', $viewData);
    }

    public function index()
    {
        // Show all available pages
        $pages = filaman_get_pages();

        return view('filaman-pages::index', [
            'pages' => $pages,
            'title' => 'All Pages',
        ]);
    }
}
