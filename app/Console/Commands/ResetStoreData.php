<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetStoreData extends Command
{
    protected $signature = 'store:reset {--products : Also reset products table}';

    protected $description = 'Reset store data (orders and order_items). WARNING: This deletes all data!';

    public function handle()
    {
        $this->warn('⚠️  WARNING: This will DELETE ALL store data!');
        $this->newLine();
        
        $this->info('Tables to be reset:');
        $this->line('  - orders');
        $this->line('  - order_items');
        
        if ($this->option('products')) {
            $this->line('  - products');
        }
        
        $this->newLine();
        
        if (!$this->confirm('Are you ABSOLUTELY SURE you want to continue?', false)) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->info('Disabling foreign key checks...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            $this->info('Truncating order_items table...');
            DB::table('order_items')->truncate();
            $this->line('✓ order_items truncated');

            $this->info('Truncating orders table...');
            DB::table('orders')->truncate();
            $this->line('✓ orders truncated');

            if ($this->option('products')) {
                $this->info('Truncating products table...');
                DB::table('products')->truncate();
                $this->line('✓ products truncated');
                
                if (Schema::hasTable('product_items')) {
                    $this->info('Truncating product_items table...');
                    DB::table('product_items')->truncate();
                    $this->line('✓ product_items truncated');
                }
            }

            $this->info('Re-enabling foreign key checks...');
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            $this->newLine();
            $this->info('✓ Store data has been reset successfully!');
            $this->info('Auto-increment counters have been reset to 1.');
            
            $this->newLine();
            $this->info('Current table status:');
            $tables = ['orders', 'order_items'];
            if ($this->option('products')) {
                $tables[] = 'products';
            }
            
            foreach ($tables as $table) {
                $count = DB::table($table)->count();
                $this->line("  - {$table}: {$count} records");
            }

            return 0;

        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
