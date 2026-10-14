<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('mobile_image_path')->nullable();
            $table->string('alt_text')->nullable();
            $table->string('target_url')->nullable();
            $table->string('button_text')->nullable();
            $table->string('placement', 32)->default('homepage_hero');
            $table->string('status', 32)->default('draft');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('open_in_new_tab')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['placement', 'is_active', 'status']);
            $table->index(['starts_at', 'ends_at']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
