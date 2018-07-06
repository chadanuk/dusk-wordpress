<?php

// Composer autoloader must be loaded before WP_PHPUNIT__DIR will be available
require_once dirname(__DIR__) . '/vendor/autoload.php';

/**
 * Compatibility with PHPUnit 6+
 */
if (class_exists('PHPUnit\Runner\Version')) {
    require_once getenv('WP_PHPUNIT__DIR') . '/includes/phpunit6-compat.php';
}

require_once(getenv('WP_PHPUNIT__DIR') . '/includes/testcase.php');


require_once getenv('WP_PHPUNIT__DIR') . '/includes/functions.php';

// tests_add_filter('muplugins_loaded', function () {
//     // test set up, plugin activation, etc.

//     foreach (new DirectoryIterator(dirname(__DIR__) . '/web/app/mu-plugins/') as $fileInfo) {
//         if($fileInfo->isDir())
//         dump($fileInfo->getFilename() . ' - ' . $fileInfo->getSize() . ' bytes');
//     }
// });
