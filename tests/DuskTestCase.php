<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Firefox\FirefoxOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            $browser = env('DUSK_BROWSER', 'chrome');

            if ($browser === 'firefox') {
                // For Firefox, we use GeckoDriver directly
                static::startGeckoDriver();
            } else {
                static::startChromeDriver(['--port=9515']);
            }
        }
    }

    /**
     * Start GeckoDriver for Firefox.
     */
    protected static function startGeckoDriver(): void
    {
        $command = 'geckodriver --port=4444 > /dev/null 2>&1 &';
        shell_exec($command);
        sleep(1); // Give GeckoDriver time to start
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $browser = env('DUSK_BROWSER', 'chrome');

        return match ($browser) {
            'firefox' => $this->createFirefoxDriver(),
            default => $this->createChromeDriver(),
        };
    }

    /**
     * Create Chrome WebDriver instance.
     */
    protected function createChromeDriver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    /**
     * Create Firefox WebDriver instance.
     */
    protected function createFirefoxDriver(): RemoteWebDriver
    {
        $options = (new FirefoxOptions);

        $arguments = [];

        if (! $this->hasHeadlessDisabled()) {
            $arguments[] = '--headless';
        }

        if (! empty($arguments)) {
            $options->addArguments($arguments);
        }

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:4444',
            DesiredCapabilities::firefox()->setCapability(
                FirefoxOptions::CAPABILITY, $options
            )
        );
    }

    /**
     * Take a screenshot with custom name and location.
     */
    protected function takeScreenshot(?string $name = null, ?string $directory = null): string
    {
        $name = $name ?: 'screenshot-'.date('Y-m-d-H-i-s');
        $directory = $directory ?: storage_path('app/screenshots');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = $directory.'/'.$name.'.png';

        // For use within existing browse() calls, just return expected path
        // The screenshot() method should be called directly in the test
        return $filename;
    }

    /**
     * Take a full page screenshot.
     */
    protected function takeFullPageScreenshot(?string $name = null, ?string $directory = null): string
    {
        $name = $name ?: 'fullpage-'.date('Y-m-d-H-i-s');
        $directory = $directory ?: storage_path('app/screenshots');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $this->browse(function ($browser) use ($name) {
            // For full page screenshots, scroll to top and capture
            $browser->script([
                'window.scrollTo(0, 0);',
                'document.body.style.height = "auto";',
            ]);

            $browser->screenshot($name);
        });

        return $directory.'/'.$name.'.png';
    }

    /**
     * Create a Filament admin user for testing.
     */
    protected function createFilamentUser(): \App\Models\User
    {
        // Try to find existing user first to avoid unique constraint errors
        $user = \App\Models\User::where('email', 'admin@test.com')->first();

        if (! $user) {
            $user = \App\Models\User::factory()->create([
                'email' => 'admin@test.com',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);
        }

        return $user;
    }

    /**
     * Login as admin user in Filament.
     */
    protected function loginAsFilamentAdmin($browser): void
    {
        $user = $this->createFilamentUser();

        $browser->visit('/admin/login')
            ->waitFor('input[name="email"]') // Wait for form to load
            ->type('email', $user->email)
            ->type('password', 'password')
            ->press('Sign in')
            ->waitForLocation('/admin', 10); // Wait up to 10 seconds
    }
}
