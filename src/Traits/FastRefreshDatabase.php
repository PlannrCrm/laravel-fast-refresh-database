<?php

namespace Plannr\Laravel\FastRefreshDatabase\Traits;

use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Plannr\Laravel\FastRefreshDatabase\Data\FastRefreshDatabaseState;

trait FastRefreshDatabase
{
    use RefreshDatabase;

    /**
     * Refresh a conventional test database.
     *
     * @return void
     * @throws \JsonException
     */
    protected function refreshTestDatabase()
    {
        if (!RefreshDatabaseState::$migrated) {
            $cachedChecksum = FastRefreshDatabaseState::$cachedChecksum ? FastRefreshDatabaseState::$cachedChecksum : $this->getCachedMigrationChecksum();
            $currentChecksum = FastRefreshDatabaseState::$currentChecksum ? FastRefreshDatabaseState::$cachedChecksum : $this->calculateMigrationChecksum();

            if ($cachedChecksum !== $currentChecksum) {
                $this->artisan('migrate:fresh', $this->migrateFreshUsing());

                $this->app[Kernel::class]->setArtisan(null);

                $this->storeMigrationChecksum($currentChecksum);
            }

            RefreshDatabaseState::$migrated = true;
        }

        $this->beginDatabaseTransaction();
    }

    /**
     * Calculate a checksum based on the migrations name and last modified date
     *
     * @return string
     * @throws \JsonException
     */
    protected function calculateMigrationChecksum(): string
    {
        $migrations = collect(app('migrator')->paths())
            // There might be loaded paths that does not exist. We need to filter those out.
            ->map(function ($path) {
                return realpath($path);
            })
            ->filter()
            ->flatMap(function ($path) {
                $finder = Finder::create()
                    ->in($path)
                    ->name('*.php')
                    ->ignoreDotFiles(true)
                    ->ignoreVCS(true)
                    ->files();

                return array_map(static function (SplFileInfo $fileInfo) {
                    return [$fileInfo->getMTime(), $fileInfo->getPath()];
                }, iterator_to_array($finder));
            })->toArray();

        // Reset the array keys so there is less data

        $migrations = array_values($migrations);

        // Add the current git branch

        $checkBranch = new Process(['git', 'branch', '--show-current']);
        $checkBranch->run();

        $migrations['gitBranch'] = trim($checkBranch->getOutput());

        // Create a hash

        return hash('sha256', json_encode($migrations, JSON_THROW_ON_ERROR));
    }

    /**
     * Get the cached migration checksum
     *
     * @return string|null
     */
    protected function getCachedMigrationChecksum(): ?string
    {
        $callbackFunc = function () {
            return file_get_contents($this->getMigrationChecksumFile());
        };
        return rescue($callbackFunc, null, false);
    }

    /**
     * Store the migration checksum
     *
     * @param string $checksum
     * @return void
     */
    protected function storeMigrationChecksum(string $checksum): void
    {
        file_put_contents($this->getMigrationChecksumFile(), $checksum);
    }

    /**
     * Provides a configurable migration checksum file path
     *
     * @return string
     */
    protected function getMigrationChecksumFile(): string
    {
        return storage_path('app/migrationChecksum.txt');
    }
}
