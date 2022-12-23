# FastRefreshDatabase for Laravel 🚀

Have you ever come across an issue where the traditional `RefreshDatabase` trait takes ages to run tests when you have lots of migrations? If so, you may be after this package!

## The Problem
Traditionally, the `RefreshDatabase` trait will run `php artisan migrate:fresh` every time you run tests. After the first test, it will use transactions to roll back the data and run the next one, so subsequent tests are fast, but the initial test is slow. This can  be really annoying if you are used to running a single test, as it could take seconds to run a single test.

## The Solution
You don't need to run `php artisan migrate:fresh` every time you run tests, only when you add a new migration or change an old one. The `FastRefreshDatabase` trait will create a checksum of your `migrations` folder as well as your current Git branch, if you are using Git and will create a `migrationChecksum.txt` file in your application. When your migrations change or your branch changes, the checksum won't match the cached one and `php artisan migrate:fresh` is run.

When you don't make any changes, it will continue to use the same database without refreshing, which can speed up the test time by 100x!

## Benchmarks
Running a single test, with about 400 migrations.

### Intel i5 Processor
Before: 30 seconds

After: 100ms (Baseline)

### Apple M1 Pro
Before: 5 seconds

After: 100ms (Baseline)

## Installation

Install the package with Composer

```bash
composer require plannr/laravel-fast-refresh-database
```

## Adding to your TestCase
Next, just replace the existing `RefreshDatabase` trait you are using in your TestCase file with the `FastRefreshDatabase` trait

```php
<?php

namespace Tests;

-use Illuminate\Foundation\Testing\RefreshDatabase;
+use App\Tests\Traits\FastRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
+   use FastRefreshDatabase;
-   use RefreshDatabase;
}
```

## Using Pest
Just replace the `uses` line in your `Pest.php` file

```php
-use Illuminate\Foundation\Testing\RefreshDatabase;
+use App\Tests\Traits\FastRefreshDatabase;

-uses(RefreshDatabase::class)->in(__DIR__);
+uses(FastRefreshDatabase::class)->in(__DIR__);
```
