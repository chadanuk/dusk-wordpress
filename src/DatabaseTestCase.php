<?php
namespace Chadanuk\DuskWordpressTests;

use Chadanuk\DuskWordpressTests\TestCase as BaseTestCase;

class DatabaseTestCase extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $dbConnection = new \PDO('mysql:host=localhost', $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);

        $dropStatement =  $dbConnection->prepare('DROP DATABASE IF EXISTS `'.$_ENV['DB_NAME'].'`');
        $dropStatement->execute();

        $statement =  $dbConnection->prepare('CREATE DATABASE IF NOT EXISTS `'.$_ENV['DB_NAME'].'`');
        $statement->execute();

        require_once(static::$baseDir.'/web/wp-config.php');

        require_once(realpath(__DIR__.'/../override-upgrade.php'));
        require_once(static::$baseDir.'/web/wp/wp-admin/includes/upgrade.php');

        require_wp_db();

        $this->wpdb = $wpdb;
        wp_install('THEME TEST', 'test.user@rareloop.com', 'test.user@rareloop.com', false, '', wp_slash('letmein'));

        switch_theme('lumberjack');
    }
}
