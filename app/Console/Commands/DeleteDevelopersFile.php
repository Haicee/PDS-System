<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class DeleteDevelopersFile extends Command
{
    protected $signature = 'developers:delete';
    protected $description = 'Delete the DEVELOPERS.txt file (requires password)';

    public function handle()
    {
        $filePath = base_path('DEVELOPERS.txt');

        if (!file_exists($filePath)) {
            $this->error('DEVELOPERS.txt does not exist.');
            return 1;
        }

        // Extract hash from file
        $content = file_get_contents($filePath);
        preg_match('/HASH:\s*(\$2y\$.+)$/', $content, $matches);
        $storedHash = trim($matches[1] ?? '');

        if (empty($storedHash)) {
            $this->error('Could not read authorization hash from file.');
            return 1;
        }

        $password = $this->secret('Enter authorization password to delete DEVELOPERS.txt');

        if (!Hash::check($password, $storedHash)) {
            $this->error('Incorrect password. Deletion denied.');
            return 1;
        }

        unlink($filePath);
        $this->info('DEVELOPERS.txt has been deleted.');
        return 0;
    }
}
