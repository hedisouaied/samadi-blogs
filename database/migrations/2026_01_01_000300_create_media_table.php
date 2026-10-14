<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('original_filename');
            $table->string('display_name');
            $table->string('mime_type', 128);
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text')->nullable();
            $table->string('caption')->nullable();
            $table->string('collection', 64)->default('default');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('mime_type');
            $table->index('collection');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
