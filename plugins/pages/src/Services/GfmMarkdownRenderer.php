<?php

namespace FilaMan\Pages\Services;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TaskList\TaskListExtension;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class GfmMarkdownRenderer
{
    protected $converter;

    public function __construct()
    {
        // Use GitHub Flavored Markdown converter with allowed HTML tags for details/summary
        $this->converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'allow',
            'allow_unsafe_links' => false,
            'disallowed_raw_html' => [
                'disallowed_tags' => ['script', 'iframe', 'object', 'embed', 'form', 'input', 'button'],
            ],
        ]);
    }

    public function render(string $markdown): string
    {
        try {
            $result = $this->converter->convert($markdown);

            return $result->getContent();
        } catch (\Exception $e) {
            // Fallback to basic markdown if GFM fails
            error_log('GFM Renderer Error: '.$e->getMessage());

            return \Illuminate\Support\Str::markdown($markdown);
        }
    }

    public function renderInline(string $markdown): string
    {
        // For inline rendering, we strip paragraph tags
        $html = $this->render($markdown);

        return preg_replace('/^<p>(.*)<\/p>$/s', '$1', trim($html));
    }

    /**
     * Create a custom GFM environment with additional extensions
     */
    public static function createCustomEnvironment(array $config = []): Environment
    {
        $environment = new Environment($config);

        // Add core CommonMark extensions
        $environment->addExtension(new \League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension);

        // Add GitHub Flavored Markdown extensions
        $environment->addExtension(new AutolinkExtension);
        $environment->addExtension(new DisallowedRawHtmlExtension);
        $environment->addExtension(new StrikethroughExtension);
        $environment->addExtension(new TableExtension);
        $environment->addExtension(new TaskListExtension);

        // Add syntax highlighting extension if needed
        if (class_exists(\League\CommonMark\Extension\ExternalLink\ExternalLinkExtension::class)) {
            $environment->addExtension(new \League\CommonMark\Extension\ExternalLink\ExternalLinkExtension);
        }

        return $environment;
    }

    /**
     * Render markdown with custom CSS classes for styling
     */
    public function renderWithClasses(string $markdown, array $classes = []): string
    {
        $html = $this->render($markdown);

        // Style tables
        $html = str_replace('<table>', '<table class="min-w-full border-collapse border border-gray-300 dark:border-gray-600 mb-4">', $html);
        $html = str_replace('<thead>', '<thead class="bg-gray-50 dark:bg-gray-700">', $html);
        $html = str_replace('<th>', '<th class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-left font-medium text-gray-900 dark:text-white">', $html);
        $html = str_replace('<td>', '<td class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-gray-700 dark:text-gray-300">', $html);

        return $html;
    }

    /**
     * Extract headings from markdown for table of contents
     */
    public function extractHeadings(string $markdown): array
    {
        $headings = [];
        $lines = explode("\n", $markdown);

        foreach ($lines as $line) {
            if (preg_match('/^(#{1,6})\s+(.+)$/', trim($line), $matches)) {
                $level = strlen($matches[1]);
                $text = trim($matches[2]);
                $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $text));
                $slug = trim($slug, '-');

                $headings[] = [
                    'level' => $level,
                    'text' => $text,
                    'slug' => $slug,
                ];
            }
        }

        return $headings;
    }

    /**
     * Add anchor links to headings
     */
    public function renderWithAnchors(string $markdown): string
    {
        $html = $this->render($markdown);

        // Add anchor links to headings
        $html = preg_replace_callback(
            '/<h([1-6])([^>]*)>(.+?)<\/h[1-6]>/',
            function ($matches) {
                $level = $matches[1];
                $attributes = $matches[2];
                $text = $matches[3];
                $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', strip_tags($text)));
                $slug = trim($slug, '-');

                return sprintf(
                    '<h%s%s id="%s"><a href="#%s" class="anchor-link">%s</a></h%s>',
                    $level,
                    $attributes,
                    $slug,
                    $slug,
                    $text,
                    $level
                );
            },
            $html
        );

        return $html;
    }
}
