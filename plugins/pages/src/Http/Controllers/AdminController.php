<?php

namespace FilaMan\Pages\Http\Controllers;

use FilaMan\Pages\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

class AdminController extends Controller
{
    public function index()
    {
        $pages = Page::getAllFromFiles();

        return view('filaman-pages::admin.index', compact('pages'));
    }

    public function create()
    {
        return view('filaman-pages::admin.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'slug' => 'required|max:255|regex:/^[a-z0-9-]+$/',
            'description' => 'max:500',
            'category' => 'required',
            'order' => 'required|integer',
            'content' => 'required',
        ]);

        $slug = $request->slug;
        $pagesPath = __DIR__.'/../../resources/views/pages/';
        $filePath = $pagesPath.$slug.'.md';

        if (File::exists($filePath)) {
            return back()->withErrors(['slug' => 'A page with this slug already exists.']);
        }

        if (! File::exists($pagesPath)) {
            File::makeDirectory($pagesPath, 0755, true);
        }

        $frontMatter = [
            'title' => $request->title,
            'description' => $request->description,
            'published' => $request->has('published'),
            'category' => $request->category,
            'order' => (int) $request->order,
            'seo_title' => $request->seo_title,
            'seo_description' => $request->seo_description,
            'custom_css' => $request->custom_css,
            'custom_js' => $request->custom_js,
        ];

        $yamlString = '---'.PHP_EOL;
        foreach ($frontMatter as $key => $value) {
            if (is_bool($value)) {
                $yamlString .= $key.': '.($value ? 'true' : 'false').PHP_EOL;
            } elseif ($value) {
                $yamlString .= $key.': '.$value.PHP_EOL;
            }
        }
        $yamlString .= '---'.PHP_EOL.PHP_EOL;

        $content = $yamlString.$request->content;
        File::put($filePath, $content);

        return redirect()->route('pages.admin.index')->with('success', 'Page created successfully!');
    }

    public function show(string $slug)
    {
        $page = Page::createFromFile($slug);
        if (! $page) {
            abort(404);
        }

        return view('filaman-pages::admin.show', compact('page'));
    }

    public function edit(string $slug)
    {
        $page = Page::createFromFile($slug);
        if (! $page) {
            abort(404);
        }

        return view('filaman-pages::admin.edit', compact('page'));
    }

    public function update(Request $request, string $slug)
    {
        $request->validate([
            'title' => 'required|max:255',
            'description' => 'max:500',
            'category' => 'required',
            'order' => 'required|integer',
            'content' => 'required',
        ]);

        $page = Page::createFromFile($slug);
        if (! $page) {
            abort(404);
        }

        $frontMatter = [
            'title' => $request->title,
            'description' => $request->description,
            'published' => $request->has('published'),
            'category' => $request->category,
            'order' => (int) $request->order,
            'seo_title' => $request->seo_title,
            'seo_description' => $request->seo_description,
            'custom_css' => $request->custom_css,
            'custom_js' => $request->custom_js,
        ];

        $yamlString = '---'.PHP_EOL;
        foreach ($frontMatter as $key => $value) {
            if (is_bool($value)) {
                $yamlString .= $key.': '.($value ? 'true' : 'false').PHP_EOL;
            } elseif ($value) {
                $yamlString .= $key.': '.$value.PHP_EOL;
            }
        }
        $yamlString .= '---'.PHP_EOL.PHP_EOL;

        $content = $yamlString.$request->content;
        File::put($page->getFilePath(), $content);

        return redirect()->route('pages.admin.index')->with('success', 'Page updated successfully!');
    }

    public function destroy(string $slug)
    {
        $page = Page::createFromFile($slug);
        if (! $page) {
            abort(404);
        }

        File::delete($page->getFilePath());

        return redirect()->route('pages.admin.index')->with('success', 'Page deleted successfully!');
    }
}
