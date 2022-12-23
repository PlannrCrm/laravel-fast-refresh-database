<?php

namespace Plannr\Laravel\FastRefreshDatabase\Data;

class FastRefreshDatabaseState
{
    /**
     * The checksum cached in the migrationChecksum.txt file
     *
     * @var string|null
     */
    public static ?string $cachedChecksum = null;

    /**
     * The current checksum calculated by the application
     *
     * @var string|null
     */
    public static ?string $currentChecksum = null;
}
