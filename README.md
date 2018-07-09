# dusk-wordpress
Laravel Dusk for WordPress (bedrock -https://roots.io/bedrock/)

## Installing
```console
composer require chadanuk/dusk-wordpress
```

##### File Structue

 We suggest adding the following file structure:

  - phpunit.xml
  - .env.dusk (with test database name in it)
  - Tests
    - Browser
      - console
      - screenshots
        - ExampleTest.php

###### phpunit.xml
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/chadanuk/dusk-wordpress/bootstrap.php"
         backupGlobals="false"
         backupStaticAttributes="false"
         colors="true"
         verbose="true"
         convertErrorsToExceptions="true"
         convertNoticesToExceptions="true"
         convertWarningsToExceptions="true"
         processIsolation="false"
         stopOnFailure="false">
    <testsuites>
        <testsuite name="Dusk Wordpress Theme Test Suite">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
    <filter>
        <whitelist>
            <directory suffix=".php">/</directory>
        </whitelist>
    </filter>
    <php>
        <env name="WP_ENV" value="testing"/>
        <env name="DB_NAME" value="database_name"/>
        <env name="DB_USER" value="root"/>
        <env name="DB_PASSWORD" value=""/>
        <env name="WP_THEME" value="theme_name"/>
        <const name="WP_INSTALLING" value="true"/>
    </php>
</phpunit>

```

## Writing Tests

The assertions available to you are documented on the Laravel site (https://laravel.com/docs/5.6/dusk#available-assertions)

###### ExampleTest.php

```php
<?php
namespace Tests\Browser;

use Chadanuk\DuskWordpressTests\Traits\WordpressPost;
use Chadanuk\DuskWordpressTests\TestCase as TestCase;

class ExampleTest extends TestCase
{
    /**
     * @test
     */
    public function can_see_home_page_title()
    {
        $postFactory = new \WP_UnitTest_Factory_For_Post();
        $postId = $postFactory->create_object([
            'post_title' => 'Home page title',
            'post_type' => 'page',
            'post_name' => 'home'
        ]);

        $this->browse(function ($browser) {
            $browser->visit('/')->screenshot('home')
                ->assertSee('Home page title');
        });
    }
}

```

## Running Tests

```console
vendor/bin/phpunit tests/Browser/ExampleTest.php
```
