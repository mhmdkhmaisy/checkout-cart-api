<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('paypal_email');
            $table->decimal('percentage', 5, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique('paypal_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
