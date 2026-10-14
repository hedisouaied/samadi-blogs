<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();
            $table->string('label');
            $table->string('type', 32)->default('custom_url');
            $table->foreignId('article_id')->nullable()->constrained('articles')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('open_in_new_tab')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['menu_id', 'sort_order']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
