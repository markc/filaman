<?php

// Global helper functions for FilaMan Pages plugin

if (! function_exists('filaman_plugin_path')) {
    function filaman_plugin_path($pluginName, $path = '')
    {
        return base_path('plugins/'.$pluginName.'/'.ltrim($path, '/'));
    }
}

if (! function_exists('filaman_get_pages')) {
    function filaman_get_pages()
    {
        $pages = [];
        $pagesDirectory = filaman_plugin_path('pages', 'resources/views/pages');

        if (\Illuminate\Support\Facades\File::isDirectory($pagesDirectory)) {
            $files = \Illuminate\Support\Facades\File::files($pagesDirectory);
            foreach ($files as $file) {
                if ($file->getExtension() === 'md') {
                    $content = \Illuminate\Support\Facades\File::get($file->getPathname());
                    $document = \Spatie\YamlFrontMatter\YamlFrontMatter::parse($content);
                    $frontMatter = $document->matter();

                    // Ensure title and slug exist for navigation
                    if (isset($frontMatter['title']) && isset($frontMatter['slug'])) {
                        $pages[] = [
                            'title' => $frontMatter['title'],
                            'slug' => $frontMatter['slug'],
                            'order' => $frontMatter['order'] ?? 999, // Default order for sorting
                            'description' => $frontMatter['description'] ?? '',
                            'published' => $frontMatter['published'] ?? true,
                        ];
                    }
                }
            }
        }

        // Sort pages by 'order' from front matter
        usort($pages, function ($a, $b) {
            return $a['order'] <=> $b['order'];
        });

        // Filter published pages only
        return array_filter($pages, fn ($page) => $page['published']);
    }
}