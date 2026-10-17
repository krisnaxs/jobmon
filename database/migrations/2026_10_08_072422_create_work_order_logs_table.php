<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('work_order_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('work_order_id')
                ->constrained('work_orders')
                ->onDelete('cascade');

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->string('action');                // created, started, completed, assigned, dll
            $table->string('from_status')->nullable(); // status sebelum
            $table->string('to_status')->nullable();   // status sesudah
            $table->text('note')->nullable();
            $table->json('meta')->nullable();          // data tambahan (fleksibel)

            $table->timestamps();

            $table->index(['work_order_id', 'created_at']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('work_order_logs');
    }
};
