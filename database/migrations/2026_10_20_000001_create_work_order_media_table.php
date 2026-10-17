<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_order_media', function (Blueprint $table) {
            $table->id();

            $table->foreignId('work_order_id')
                ->constrained('work_orders')
                ->onDelete('cascade');

            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->onDelete('cascade');
            $table->enum('type', ['before', 'after', 'other'])->default('other');
            $table->enum('media_type', ['image', 'video'])->default('image');

            $table->string('path');            // storage path
            $table->string('original_name')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable(); // bytes
            $table->text('caption')->nullable();

            $table->timestamps();

            $table->index(['work_order_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_media');
    }
};
