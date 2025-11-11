<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE webhooks MODIFY COLUMN event_type ENUM('promotion.created', 'promotion.claimed', 'promotion.limit_reached', 'promotion.expired', 'update.published') DEFAULT 'promotion.created'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE webhooks MODIFY COLUMN event_type ENUM('promotion.created', 'promotion.claimed', 'update.published') DEFAULT 'promotion.created'");
    }
};
