<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_logs', function (Blueprint $table) {
            $table->id();
            $table->string('metric_type', 32)->index();
            $table->string('identifier', 128)->nullable();
            $table->float('value');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->index();
            
            $table->index(['metric_type', 'created_at']);
            $table->index(['identifier', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_logs');
    }
};
