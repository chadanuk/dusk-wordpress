<?php
namespace Chadanuk\DuskWordpressTests;

use Laravel\Dusk\Browser;
use duncan3dc\Laravel\Dusk;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Chadanuk\DuskWordpressTests\Console\DuskCommand;


class TestCase extends \WP_UnitTestCase
{
    use ProvidesBrowser;
    public static $vendorDir;
    public static $baseDir;
    public static $duskCommand;
    public static $tablePrefix;

    protected static $wpdb;

    public static function setUpBeforeClass()
    {
        static::$baseDir = realpath(__DIR__ . '/../../');
        static::$vendorDir = static::$baseDir . '/vendor/';

        require_once(static::$baseDir . '/vendor/autoload.php');
        require_once(static::$baseDir . '/config/application.php');

        static::$tablePrefix = $table_prefix;
        static::setUpCleanDb();
        static::setUpWpTestEnvironment();

        static::$duskCommand = new DuskCommand(static::$baseDir);
        static::$duskCommand->execute(new ArrayInput([]), new ConsoleOutput());
        dump('hi');
        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass()
    {
        static::$duskCommand->cleanUp();
        parent::tearDownAfterClass();
    }

    public function setUp()
    {
        switch_theme('lumberjack');

        // Delete any default posts & related data
        _delete_all_posts();

        $this->initialiseBrowser();
        parent::setUp();

        $_SERVER[' SERVER_PROTOCOL '] = ' ';
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

    public static function setUpCleanDb()
    {
        $dbConnection = new \PDO('mysql:host=' . env('DB_HOST'), $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
        $dropStatement = $dbConnection->prepare('DROP DATABASE IF EXISTS `' . $_ENV['DB_NAME'] . '`');
        $dropStatement->execute();

        $statement = $dbConnection->prepare('CREATE DATABASE IF NOT EXISTS `' . $_ENV['DB_NAME'] . '`');
        $statement->execute();
    }

    public static function setUpWpTestEnvironment()
    {
        require_once(getenv('WP_PHPUNIT__DIR') . '/includes/functions.php');

        // Override the PHPMailer
        require_once(getenv('WP_PHPUNIT__DIR') . '/includes/mock-mailer.php');
        $phpmailer = new \MockPHPMailer(true);

        $wp_theme_directories = array(static::$baseDir . '/web/app/themes');

        $GLOBALS['_wp_die_disabled'] = false;
        // Allow tests to override wp_die
        tests_add_filter('wp_die_handler', '_wp_die_handler_filter');

        // Preset WordPress options defined in bootstrap file.
        // Used to activate themes, plugins, as well as  other settings.
        if (isset($GLOBALS['wp_tests_options'])) {
            function wp_tests_options($value)
            {
                $key = substr(current_filter(), strlen('pre_option_'));
                return $GLOBALS['wp_tests_options'][$key];
            }

            foreach (array_keys($GLOBALS['wp_tests_options']) as $key) {
                tests_add_filter('pre_option_' . $key, 'wp_tests_options');
            }
        }
        $table_prefix = static::$tablePrefix;

        require_once(static::$baseDir . '/web/wp/wp-settings.php');

        require_once(static::$baseDir . '/web/wp/wp-admin/includes/upgrade.php');
        wp_install('THEME TEST', 'test.user@rareloop.com', 'test.user@rareloop.com', false, '', wp_slash('letmein'));
    }

    function start_transaction()
    {
        global $wpdb;
        $wpdb->query('SET autocommit = 1;');

    }
}


/**
 * A child class of the PHP test runner.
 *
 * Used to access the protected longOptions property, to parse the arguments
 * passed to the script.
 *
 * If it is determined that phpunit was called with a --group that corresponds
 * to an @ticket annotation (such as `phpunit --group 12345` for bugs marked
 * as #WP12345), then it is assumed that known bugs should not be skipped.
 *
 * If WP_TESTS_FORCE_KNOWN_BUGS is already set in wp-tests-config.php, then
 * how you call phpunit has no effect.
 */
class WP_PHPUnit_Util_Getopt extends \PHPUnit_Util_Getopt
{
    protected $longOptions = array(
        ' exclude - group = ',
        ' group = ',
    );
    function __construct($argv)
    {
        array_shift($argv);
        $options = array();
        while (current($argv)) {
            $arg = current($argv);
            next($argv);
            try {
                if (strlen($arg) > 1 && $arg[0] === ' - ' && $arg[1] === ' - ') {
                    \PHPUnit_Util_Getopt::parseLongOption(substr($arg, 2), $this->longOptions, $options, $argv);
                }
            } catch (\PHPUnit_Framework_Exception $e) {
				// Enforcing recognized arguments or correctly formed arguments is
				// not really the concern here.
                continue;
            }
        }

        $skipped_groups = array(
            ' ajax ' => true,
            ' ms - files ' => true,
            ' external - http ' => true,
        );

        foreach ($options as $option) {
            switch ($option[0]) {
                case ' --exclude - group ':
                    foreach ($skipped_groups as $group_name => $skipped) {
                        $skipped_groups[$group_name] = false;
                    }
                    continue 2;
                case ' --group':
                    $groups = explode(' , ', $option[1]);
                    foreach ($groups as $group) {
                        if (is_numeric($group) || preg_match(' / ^ (UT | Plugin) \d + $ / ', $group)) {
                            WP_UnitTestCase::forceTicket($group);
                        }
                    }

                    foreach ($skipped_groups as $group_name => $skipped) {
                        if (in_array($group_name, $groups)) {
                            $skipped_groups[$group_name] = false;
                        }
                    }
                    continue 2;
            }
        }

        $skipped_groups = array_filter($skipped_groups);
        foreach ($skipped_groups as $group_name => $skipped) {
            echo sprintf(' Not running % 1 $s tests . To execute these, use -- group % 1 $s . ', $group_name, false) . PHP_EOL;
        }

        if (!isset($skipped_groups[' external - http '])) {
            echo PHP_EOL;
            echo ' External HTTP skipped tests can be caused by timeouts . ' . PHP_EOL;
            echo '

        if this changeset includes changes to HTTP, make sure there are no timeouts . ' . PHP_EOL;
            echo PHP_EOL;
        }
    }
}
new WP_PHPUnit_Util_Getopt($_SERVER['argv']);
