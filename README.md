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

## Writing Tests

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
