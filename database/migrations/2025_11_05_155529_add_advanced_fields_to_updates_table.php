<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('updates', function (Blueprint $table) {
            $table->boolean('is_published')->default(true)->after('client_update');
            $table->boolean('is_featured')->default(false)->after('is_published');
            $table->boolean('is_pinned')->default(false)->after('is_featured');
            $table->text('excerpt')->nullable()->after('content');
            $table->string('featured_image')->nullable()->after('excerpt');
            $table->string('category')->nullable()->after('featured_image');
            $table->string('author')->nullable()->after('category');
            $table->text('meta_description')->nullable()->after('author');
            $table->unsignedBigInteger('views')->default(0)->after('meta_description');
            $table->timestamp('published_at')->nullable()->after('views');
            
            $table->index('is_published');
            $table->index('is_featured');
            $table->index('is_pinned');
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('updates', function (Blueprint $table) {
            $table->dropColumn([
                'is_published',
                'is_featured',
                'is_pinned',
                'excerpt',
                'featured_image',
                'category',
                'author',
                'meta_description',
                'views',
                'published_at'
            ]);
        });
    }
};
