<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convert tables to InnoDB to support transactions and foreign keys
        DB::statement('ALTER TABLE orders ENGINE = InnoDB');
        DB::statement('ALTER TABLE order_items ENGINE = InnoDB');
        DB::statement('ALTER TABLE products ENGINE = InnoDB');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally convert back (not recommended in production)
        // DB::statement('ALTER TABLE orders ENGINE = MyISAM');
        // DB::statement('ALTER TABLE order_items ENGINE = MyISAM');
        // DB::statement('ALTER TABLE products ENGINE = MyISAM');
    }
};
