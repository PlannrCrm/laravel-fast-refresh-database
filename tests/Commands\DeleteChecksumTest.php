<?php

use Illuminate\Support\Facades\Storage;

it('can delete the checksum file', function () {
    Storage::disk('local')->put('migrationChecksum.txt', 'Contents');
    Storage::disk('local')->assertExists('migrationChecksum.txt');

    $this->artisan('delete-checksum')->expectsOutputToContain('Checksum deleted successfully.');

    Storage::disk('local')->assertMissing('migrationChecksum.txt');
});

it('display message when there is no file to delete', function () {
    $this->artisan('delete-checksum')->expectsOutputToContain('Nothing to delete.');
});
