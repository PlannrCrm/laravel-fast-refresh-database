# FastRefreshDatabase for Laravel 🚀

Have you ever come across an issue where the traditional `RefreshDatabase` trait takes ages to run tests when you have lots of migrations? If so, you may be after this package!

## The Problem
Traditionally, the `RefreshDatabase` trait will run `php artisan migrate:fresh` every time you run tests. After the first test, it will use transactions to roll back the data and run the next one, so subsequent tests are fast, but the initial test is slow. This can  be really annoying if you are used to running a single test, as it could take seconds to run a single test.

## The Solution
You don't need to run `php artisan migrate:fresh` every time you run tests, only when you add a new migration or change an old one. The `FastRefreshDatabase` trait will create a checksum of your `migrations` folder as well as your current Git branch. It will then create a checksum file in your application's `storage/app` directory. When your migrations change or your branch changes, the checksum won't match the cached one and `php artisan migrate:fresh` is run.

When you don't make any changes, it will continue to use the same database without refreshing, which can speed up the test time by 100x!

## Benchmarks
Running a single test, with about 400 migrations.

| Processor     | Before     | After |
|---------------|------------|-------|
| Intel Core i5 | 30 seconds | 100 milliseconds |
| Apple M1 Pro  | 5 seconds  | 100 milliseconds |

## Installation

Install the package with Composer

```bash
composer require plannr/laravel-fast-refresh-database --dev
```

## Adding to your TestCase
Next, just replace the existing `RefreshDatabase` trait you are using in your TestCase file with the `FastRefreshDatabase` trait

```diff
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
-use Illuminate\Foundation\Testing\RefreshDatabase;
+use Plannr\Laravel\FastRefreshDatabase\Traits\FastRefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
-   use RefreshDatabase;
+   use FastRefreshDatabase;
}
```

## Using Pest
Just replace the `uses` line in your `Pest.php` file

```diff
-use Illuminate\Foundation\Testing\RefreshDatabase;
+use Plannr\Laravel\FastRefreshDatabase\Traits\FastRefreshDatabase;

-uses(RefreshDatabase::class)->in(__DIR__);
uses(FastRefreshDatabase::class)->in('Feature');
```

## Deleting The Migration Checksum

Sometimes you may wish to force-update database migrations, to do this, locate the `migration-checksum_{Database Name Slug}.txt` file within `storage/app`.

## Customising the checksum file location

You may customise the migration checksum file location and name by extending the trait and overwriting the `getMigrationChecksumFile()` method.

```php
protected function getMigrationChecksumFile(): string
{
    return storage_path('custom/some-other-file.txt');
}
```

### ParaTest Databases

Parallel testing databases contain tokens that serve as unique identifiers for each test runner. This makes the trait inherently able to support parallel testing without any extra effort, because the database name is stored in the checksum file name.
