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
    protected function refreshTestDatabase(): void
    {
        if (! RefreshDatabaseState::$migrated) {
            $cachedChecksum = FastRefreshDatabaseState::$cachedChecksum ??= $this->getCachedMigrationChecksum();
            $currentChecksum = FastRefreshDatabaseState::$currentChecksum ??= $this->calculateMigrationChecksum();

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
        // Filter out non-existing paths
        $paths = collect($this->getMigrationPaths())
            ->map(fn ($path) => realpath($path))
            ->toArray();

        $finder = Finder::create()
            ->in($paths)
            ->name('*.php')
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
            ->files();

        // Get all the migration files and their last modified date
        $migrations = collect(iterator_to_array($finder))
            ->map(fn (SplFileInfo $fileInfo) => [$fileInfo->getMTime()])
            // Reset the array keys so there is less data
            ->values()
            ->toArray();

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
        return rescue(fn () => file_get_contents($this->getMigrationChecksumFile()), null, false);
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
     * The paths that should be used to discover migrations
     *
     * @return array<string>
     */
    protected function getMigrationPaths(): array
    {
        return [
            database_path('migrations'),
            ...app('migrator')->paths(),
            ...$this->getCustomMigrationPaths(),
        ];
    }

    /**
     * Custom migration paths that should be used when discovering migrations
     *
     * @return array<string>
     */
    protected function getCustomMigrationPaths(): array
    {
        return [];
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
