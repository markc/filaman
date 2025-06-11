<?php

use FilaMan\Admin\Tests\TestCase;

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

pest()->extend(TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature', 'Unit', 'Integration');

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

expect()->extend('toBeValidPluginName', function () {
    return $this->toMatch('/^[a-zA-Z0-9_-]+$/');
});

expect()->extend('toBeValidVersion', function () {
    return $this->toMatch('/^\d+\.\d+\.\d+$/');
});

expect()->extend('toHavePluginStructure', function () {
    return $this->toHaveKey('name')
        ->and($this->toHaveKey('description'))
        ->and($this->toHaveKey('version'))
        ->and($this->toHaveKey('enabled'));
});

expect()->extend('toBeEnabledPlugin', function () {
    return $this->toHaveKey('enabled')->and($this->enabled)->toBe(true);
});

expect()->extend('toBeDisabledPlugin', function () {
    return $this->toHaveKey('enabled')->and($this->enabled)->toBe(false);
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

function createTestPluginData(array $overrides = []): array
{
    $faker = \Faker\Factory::create();
    return array_merge([
        'name' => $faker->unique()->slug(2),
        'display_name' => $faker->words(2, true),
        'description' => $faker->sentence(),
        'version' => $faker->randomElement(['1.0.0', '1.1.0', '2.0.0']),
        'enabled' => true,
        'settings' => [],
        'metadata' => [],
        'author' => $faker->name(),
        'url' => $faker->url(),
    ], $overrides);
}

function createTestComposerData(string $name, array $overrides = []): array
{
    return array_merge([
        'name' => "filaman/{$name}",
        'description' => "Test plugin {$name}",
        'type' => 'laravel-plugin',
        'version' => '1.0.0',
        'authors' => [
            ['name' => 'Test Author', 'email' => 'test@example.com']
        ],
        'require' => ['php' => '^8.3'],
        'autoload' => [
            'psr-4' => ["FilaMan\\".str($name)->studly()."\\" => 'src/']
        ]
    ], $overrides);
}

function assertPluginManagerHasPlugin(string $pluginName): void
{
    $pluginManager = app(\FilaMan\Admin\Services\PluginManager::class);
    $availablePlugins = $pluginManager->getAvailablePlugins();
    
    expect($availablePlugins)->toHaveKey($pluginName);
}

function assertPluginManagerDoesNotHavePlugin(string $pluginName): void
{
    $pluginManager = app(\FilaMan\Admin\Services\PluginManager::class);
    $availablePlugins = $pluginManager->getAvailablePlugins();
    
    expect($availablePlugins)->not()->toHaveKey($pluginName);
}

function getPluginFromManager(string $pluginName): ?array
{
    $pluginManager = app(\FilaMan\Admin\Services\PluginManager::class);
    $availablePlugins = $pluginManager->getAvailablePlugins();
    
    return $availablePlugins[$pluginName] ?? null;
}

function assertPluginHasComposerFile(string $pluginName): void
{
    $pluginPath = base_path("plugins/{$pluginName}");
    $composerFile = $pluginPath . '/composer.json';
    
    expect($composerFile)->toBeFile();
    
    $composerData = json_decode(file_get_contents($composerFile), true);
    expect($composerData)->toBeArray()
        ->and($composerData)->toHaveKey('name')
        ->and($composerData)->toHaveKey('type')
        ->and($composerData['type'])->toBe('laravel-plugin');
}

function assertPluginIsInstalled(string $pluginName): void
{
    test()->assertDatabaseHas('plugins', ['name' => $pluginName]);
}

function assertPluginIsNotInstalled(string $pluginName): void
{
    test()->assertDatabaseMissing('plugins', ['name' => $pluginName]);
}

function assertPluginIsEnabled(string $pluginName): void
{
    test()->assertDatabaseHas('plugins', ['name' => $pluginName, 'enabled' => true]);
}

function assertPluginIsDisabled(string $pluginName): void
{
    test()->assertDatabaseHas('plugins', ['name' => $pluginName, 'enabled' => false]);
}

function createTemporaryTestPlugin(string $name, array $composerOverrides = []): string
{
    $pluginPath = base_path("plugins/{$name}");
    \Illuminate\Support\Facades\File::ensureDirectoryExists($pluginPath);
    
    $composerData = createTestComposerData($name, $composerOverrides);
    file_put_contents($pluginPath . '/composer.json', json_encode($composerData, JSON_PRETTY_PRINT));
    
    return $pluginPath;
}

function removeTemporaryTestPlugin(string $name): void
{
    $pluginPath = base_path("plugins/{$name}");
    if (\Illuminate\Support\Facades\File::exists($pluginPath)) {
        \Illuminate\Support\Facades\File::deleteDirectory($pluginPath);
    }
}

function mockPluginManager(): \FilaMan\Admin\Services\PluginManager
{
    $mock = Mockery::mock(\FilaMan\Admin\Services\PluginManager::class);
    app()->instance(\FilaMan\Admin\Services\PluginManager::class, $mock);
    
    return $mock;
}

function assertFileContainsPlugin(string $filePath, string $pluginName): void
{
    expect($filePath)->toBeFile();
    
    $contents = file_get_contents($filePath);
    expect($contents)->toContain($pluginName);
}

function assertFileDoesNotContainPlugin(string $filePath, string $pluginName): void
{
    if (file_exists($filePath)) {
        $contents = file_get_contents($filePath);
        expect($contents)->not()->toContain($pluginName);
    }
}

function measurePluginOperationTime(callable $operation): float
{
    $start = microtime(true);
    $operation();
    $end = microtime(true);
    
    return ($end - $start) * 1000; // Return in milliseconds
}

function assertPluginOperationPerformance(callable $operation, int $maxTimeMs = 1000): void
{
    $time = measurePluginOperationTime($operation);
    expect($time)->toBeLessThan($maxTimeMs, "Operation took {$time}ms, expected under {$maxTimeMs}ms");
}