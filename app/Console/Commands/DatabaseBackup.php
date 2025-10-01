<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'db:backup {--compress : Compress the backup file}';

    /**
     * The console command description.
     */
    protected $description = 'Create a database backup';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Creating database backup...');

        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver !== 'mysql') {
            $this->error("Database backup is only supported for MySQL. Current driver: {$driver}");
            return Command::FAILURE;
        }

        $config = $connection->getConfig();
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];
        $host = $config['host'];
        $port = $config['port'] ?? 3306;

        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "backup_{$database}_{$timestamp}.sql";
        
        if ($this->option('compress')) {
            $filename .= '.gz';
        }

        $backupPath = storage_path("app/backups/{$filename}");
        
        // Ensure backup directory exists
        if (!is_dir(dirname($backupPath))) {
            mkdir(dirname($backupPath), 0755, true);
        }

        // Build mysqldump command
        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database)
        );

        if ($this->option('compress')) {
            $command .= ' | gzip';
        }

        $command .= " > " . escapeshellarg($backupPath);

        // Execute backup
        $result = shell_exec($command . ' 2>&1');
        
        if (file_exists($backupPath) && filesize($backupPath) > 0) {
            $size = $this->formatBytes(filesize($backupPath));
            $this->info("Database backup created successfully: {$filename} ({$size})");
            
            // Clean up old backups (keep last 7 days)
            $this->cleanupOldBackups();
            
            return Command::SUCCESS;
        } else {
            $this->error("Database backup failed: {$result}");
            return Command::FAILURE;
        }
    }

    private function cleanupOldBackups(): void
    {
        $backupDir = storage_path('app/backups');
        $files = glob($backupDir . '/backup_*.sql*');
        
        $cutoff = now()->subDays(7)->timestamp;
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $this->line("Removed old backup: " . basename($file));
            }
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}