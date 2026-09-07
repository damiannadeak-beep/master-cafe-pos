<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DatabaseBackup extends Command
{
    protected $signature = 'db:backup';
    protected $description = 'Backup database menggunakan PHP murni (tanpa mysqldump/proc_open)';

    public function handle()
    {
        $this->info('Memulai backup database...');

        try {
            $dbName = config('database.connections.mysql.database');
            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "backup_{$dbName}_{$timestamp}.sql";
            $zipFilename = "backup_{$dbName}_{$timestamp}.zip";

            $backupDir = 'backups';
            if (!Storage::exists($backupDir)) {
                Storage::makeDirectory($backupDir);
            }

            $sqlContent = $this->generateDump($dbName);

            $sqlPath = storage_path("app/{$backupDir}/{$filename}");
            file_put_contents($sqlPath, $sqlContent);

            $zipPath = storage_path("app/{$backupDir}/{$zipFilename}");
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                $zip->addFile($sqlPath, $filename);
                $zip->close();
                unlink($sqlPath);

                $sizeMB = round(filesize($zipPath) / 1024 / 1024, 2);
                $this->info("Backup berhasil disimpan!");
                $this->info("Lokasi: storage/app/{$backupDir}/{$zipFilename}");
                $this->info("Ukuran: {$sizeMB} MB");
            } else {
                $sizeMB = round(filesize($sqlPath) / 1024 / 1024, 2);
                $this->warn("ZipArchive tidak tersedia, file disimpan sebagai .sql");
                $this->info("Lokasi: storage/app/{$backupDir}/{$filename}");
                $this->info("Ukuran: {$sizeMB} MB");
            }

            $this->cleanOldBackups($backupDir, 7);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Backup gagal: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function generateDump(string $dbName): string
    {
        $pdo = DB::connection()->getPdo();
        $sql = "";

        $sql .= "-- Master Cafe POS - Database Backup\n";
        $sql .= "-- Tanggal: " . now()->format('d M Y H:i:s') . "\n";
        $sql .= "-- Database: {$dbName}\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $sql .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n";

        $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
        $this->info("Ditemukan " . count($tables) . " tabel untuk di-backup...");

        foreach ($tables as $table) {
            $this->line("   Memproses tabel: {$table}");

            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
            $sql .= $createStmt['Create Table'] . ";\n\n";

            $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);

            if (count($rows) > 0) {
                $chunks = array_chunk($rows, 500);
                foreach ($chunks as $chunk) {
                    $columns = array_keys($chunk[0]);
                    $columnList = '`' . implode('`, `', $columns) . '`';
                    $sql .= "INSERT INTO `{$table}` ({$columnList}) VALUES\n";

                    $values = [];
                    foreach ($chunk as $row) {
                        $rowValues = [];
                        foreach ($row as $value) {
                            if (is_null($value)) {
                                $rowValues[] = 'NULL';
                            } else {
                                $rowValues[] = $pdo->quote($value);
                            }
                        }
                        $values[] = '(' . implode(', ', $rowValues) . ')';
                    }
                    $sql .= implode(",\n", $values) . ";\n\n";
                }
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        return $sql;
    }

    private function cleanOldBackups(string $directory, int $keepDays): void
    {
        $files = Storage::files($directory);
        $threshold = now()->subDays($keepDays)->timestamp;
        $deleted = 0;

        foreach ($files as $file) {
            if (Storage::lastModified($file) < $threshold) {
                Storage::delete($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->info("{$deleted} file backup lama telah dihapus (lebih dari {$keepDays} hari).");
        }
    }
}