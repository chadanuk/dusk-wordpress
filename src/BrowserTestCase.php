<?php
namespace Chadanuk\DuskWordpressTests;

use Closure;
use Exception;
use Throwable;
use ReflectionFunction;
use Laravel\Dusk\Browser;
use duncan3dc\Laravel\Dusk;

use Laravel\Dusk\Concerns\ProvidesBrowser;
use \Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Chadanuk\DuskWordpressTests\Traits\HasDatabase;
use Symfony\Component\Console\Output\ConsoleOutput;
use Chadanuk\DuskWordpressTests\Console\DuskCommand;
use Chadanuk\DuskWordpressTests\TestCase as BaseTestCase;

class BrowserTestCase extends BaseTestCase
{
    use ProvidesBrowser;

    public static $duskCommand;

    public static function setUpBeforeClass()
    {
        static::$baseDir = realpath(__DIR__ . '/../../');
        static::$vendorDir = static::$baseDir . '/vendor/';

        static::$duskCommand = new DuskCommand(static::$baseDir);

        static::$duskCommand->execute(new ArrayInput([]), new ConsoleOutput());

        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass()
    {
        static::$duskCommand->cleanUp();
    }

    /**
     * Register the base URL with Dusk.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->initialiseBrowser();
    }

    public function initialiseBrowser()
    {
        // Initialise browser
        $this->browser = new Dusk;

        $screenshotsPath = static::$baseDir . '/tests/Browser/screenshots';
        @mkdir($screenshotsPath, 0777);

        $consoleLogPath = static::$baseDir . '/tests/Browser/console';
        @mkdir($consoleLogPath, 0777);

        Browser::$storeScreenshotsAt = $screenshotsPath;
        Browser::$storeConsoleLogAt = $consoleLogPath;
        Browser::$baseUrl = env('WP_HOME');
    }

    protected function driver()
    {
        return $this->browser->getDriver();
    }

}
