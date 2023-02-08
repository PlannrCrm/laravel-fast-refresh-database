<?php

namespace Plannr\Laravel\FastRefreshDatabase\Commands;

use Illuminate\Console\Command;
use Plannr\Laravel\FastRefreshDatabase\Traits\FastRefreshDatabase;

class DeleteChecksum extends Command
{
    use FastRefreshDatabase;

    public $signature = 'delete-checksum';

    public $description = 'Deletes migration checksum file';

    public function handle()
    {
        if (file_exists($this->getMigrationChecksumFile())) {
            unlink($this->getMigrationChecksumFile())
                ? $this->info('Checksum deleted successfully.')
                : $this->error('Unable to delete the checksum.');
        } else {
            $this->warn('Nothing to delete.');
        }
    }
}
