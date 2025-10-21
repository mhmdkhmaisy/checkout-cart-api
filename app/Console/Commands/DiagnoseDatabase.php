<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DiagnoseDatabase extends Command
{
    protected $signature = 'db:diagnose {--fix : Automatically fix issues}';

    protected $description = 'Diagnose database table engines and auto-increment issues';

    public function handle()
    {
        $this->info('🔍 Diagnosing Database Tables...');
        $this->newLine();

        $tables = ['orders', 'order_items', 'products'];
        $issues = [];

        foreach ($tables as $table) {
            $this->info("Checking table: {$table}");
            
            $result = DB::selectOne("
                SELECT 
                    TABLE_NAME,
                    ENGINE,
                    AUTO_INCREMENT,
                    TABLE_ROWS
                FROM information_schema.TABLES 
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
            ", [$table]);

            if (!$result) {
                $this->error("  ❌ Table '{$table}' not found!");
                continue;
            }

            $maxId = DB::selectOne("SELECT MAX(id) as max_id FROM {$table}");
            $maxId = $maxId->max_id ?? 0;
            $expectedAutoIncrement = $maxId + 1;

            $this->line("  Engine: {$result->ENGINE}");
            $this->line("  Auto-Increment: {$result->AUTO_INCREMENT}");
            $this->line("  Max ID: {$maxId}");
            $this->line("  Expected Auto-Increment: {$expectedAutoIncrement}");

            if ($result->ENGINE !== 'InnoDB') {
                $this->error("  ❌ CRITICAL: Using {$result->ENGINE} instead of InnoDB!");
                $issues[] = [
                    'table' => $table,
                    'type' => 'engine',
                    'current' => $result->ENGINE,
                    'expected' => 'InnoDB'
                ];
            } else {
                $this->info("  ✓ Engine is InnoDB");
            }

            if ($result->AUTO_INCREMENT && $result->AUTO_INCREMENT != $expectedAutoIncrement) {
                $this->warn("  ⚠ Auto-increment mismatch! Should be {$expectedAutoIncrement}");
                $issues[] = [
                    'table' => $table,
                    'type' => 'auto_increment',
                    'current' => $result->AUTO_INCREMENT,
                    'expected' => $expectedAutoIncrement
                ];
            } else {
                $this->info("  ✓ Auto-increment is correct");
            }

            $this->newLine();
        }

        if (empty($issues)) {
            $this->info('✅ No issues found! Database is healthy.');
            return 0;
        }

        $this->warn('⚠️  Found ' . count($issues) . ' issue(s)');
        
        if ($this->option('fix')) {
            $this->fixIssues($issues);
        } else {
            $this->newLine();
            $this->info('To automatically fix these issues, run:');
            $this->line('  php artisan db:diagnose --fix');
        }

        return 1;
    }

    protected function fixIssues(array $issues)
    {
        $this->newLine();
        $this->warn('🔧 Attempting to fix issues...');
        $this->newLine();

        foreach ($issues as $issue) {
            $table = $issue['table'];

            if ($issue['type'] === 'engine') {
                $this->info("Converting {$table} to InnoDB...");
                try {
                    DB::statement("ALTER TABLE {$table} ENGINE = InnoDB");
                    $this->info("  ✓ {$table} converted to InnoDB");
                } catch (\Exception $e) {
                    $this->error("  ❌ Failed: " . $e->getMessage());
                }
            }

            if ($issue['type'] === 'auto_increment') {
                $this->info("Fixing auto-increment for {$table}...");
                try {
                    $expected = $issue['expected'];
                    DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = {$expected}");
                    $this->info("  ✓ {$table} auto-increment set to {$expected}");
                } catch (\Exception $e) {
                    $this->error("  ❌ Failed: " . $e->getMessage());
                }
            }
        }

        $this->newLine();
        $this->info('✅ Fix complete! Run the command again to verify.');
    }
}
