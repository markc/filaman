<?php

use FilaMan\PagesPlugin\Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(TestCase::class)->in('Feature', 'Unit', 'Integration');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

expect()->extend('toBeValidSlug', function () {
    return $this->toMatch('/^[a-zA-Z0-9_-]+$/');
});

expect()->extend('toContainValidHtml', function () {
    return $this->toContain('<')->and($this->toContain('>'));
});

expect()->extend('toBePublishedPage', function () {
    return $this->toHaveKey('published')->and($this->published)->toBe(true);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the amount of code you need to type in your tests.
|
*/

function createTestPage(string $slug, array $frontMatter = [], string $content = 'Test content'): string
{
    $yamlFrontMatter = '';
    if (! empty($frontMatter)) {
        $yamlFrontMatter = "---\n";
        foreach ($frontMatter as $key => $value) {
            $yamlFrontMatter .= "{$key}: {$value}\n";
        }
        $yamlFrontMatter .= "---\n\n";
    }

    return $yamlFrontMatter.$content;
}

function getTestPagesPath(): string
{
    return base_path('packages/pages-plugin/resources/views/pages');
}

function assertPageIsAccessible(string $slug): void
{
    test()->get("/pages/{$slug}")->assertStatus(200);
}

function assertPageHasTitle(string $slug, string $expectedTitle): void
{
    test()->get("/pages/{$slug}")
        ->assertSee($expectedTitle)
        ->assertSee("<title>{$expectedTitle}", false);
}

function assertPageHasNavigation(string $slug): void
{
    test()->get("/pages/{$slug}")
        ->assertSee('<nav', false)
        ->assertSee('href="/pages/', false);
}

function assertPageIsSecure(string $slug): void
{
    $response = test()->get("/pages/{$slug}");
    $content = $response->getContent();

    expect($content)
        ->not->toContain('<script>alert')
        ->not->toContain('javascript:')
        ->not->toContain('onclick=');
}

function measurePageRenderTime(string $slug): float
{
    $start = microtime(true);
    test()->get("/pages/{$slug}");
    $end = microtime(true);

    return ($end - $start) * 1000; // Return in milliseconds
}

function getNavigationFromPage(string $slug): string
{
    $response = test()->get("/pages/{$slug}");
    $content = $response->getContent();

    preg_match('/<nav.*?<\/nav>/s', $content, $matches);

    return $matches[0] ?? '';
}

function assertValidMarkdownRendering(string $slug): void
{
    $response = test()->get("/pages/{$slug}");
    $content = $response->getContent();

    // Should contain HTML elements from Markdown conversion
    expect($content)
        ->toContain('<p>')
        ->toContain('<h1>')
        ->not->toContain('---'); // No YAML front matter should be visible
}

function createTemporaryTestPage(string $slug, string $content): string
{
    $filePath = getTestPagesPath()."/{$slug}.md";
    file_put_contents($filePath, $content);

    return $filePath;
}

function removeTemporaryTestPage(string $filePath): void
{
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

function assertResponseTime(string $url, int $maxTimeMs): void
{
    $start = microtime(true);
    test()->get($url);
    $end = microtime(true);

    $timeMs = ($end - $start) * 1000;
    expect($timeMs)->toBeLessThan($maxTimeMs, "Response took {$timeMs}ms, expected under {$maxTimeMs}ms");
}

function assertAccessibility(string $slug): void
{
    $response = test()->get("/pages/{$slug}");
    $content = $response->getContent();

    // Basic accessibility checks
    expect($content)
        ->toContain('<nav')
        ->toContain('role=')
        ->toContain('<main')
        ->toContain('<h1>');
}

function assertSeoTags(string $slug, string $expectedTitle, ?string $expectedDescription = null): void
{
    $response = test()->get("/pages/{$slug}");
    $content = $response->getContent();

    expect($content)
        ->toContain("<title>{$expectedTitle}")
        ->toContain('<meta name="description"')
        ->toContain('<meta property="og:');

    if ($expectedDescription) {
        expect($content)->toContain("content=\"{$expectedDescription}\"");
    }
}
