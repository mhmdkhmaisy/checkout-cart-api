<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_summaries', function (Blueprint $table) {
            $table->id();
            $table->string('timeframe', 10);
            $table->float('avg_request_time')->nullable();
            $table->bigInteger('max_memory_usage')->nullable();
            $table->float('avg_cpu_load')->nullable();
            $table->string('slowest_route', 128)->nullable();
            $table->float('slowest_route_time')->nullable();
            $table->integer('total_requests')->default(0);
            $table->integer('failed_requests')->default(0);
            $table->integer('slow_queries_count')->default(0);
            $table->float('avg_query_time')->nullable();
            $table->integer('failed_jobs')->default(0);
            $table->timestamp('created_at')->index();
            
            $table->index(['timeframe', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_summaries');
    }
};
