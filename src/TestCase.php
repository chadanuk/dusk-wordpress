<?php
namespace Chadanuk\DuskWordpressTests;

class TestCase extends \WP_UnitTestCase {
    public static $vendorDir;
    public static $baseDir;

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();

        static::$vendorDir = dirname(__DIR__);
        static::$baseDir = dirname(static::$vendorDir);
    }

    public function setUp() {
        parent::setUp();

        $_SERVER['SERVER_PROTOCOL'] = '';

        require_once(static::$baseDir.'/config/application.php');
        require_once(static::$baseDir.'/web/wp-config.php');

        require_once(static::$baseDir.'/web/index.php');

    }
}
