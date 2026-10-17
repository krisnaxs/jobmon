<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category', 50)->nullable();

            $table->enum('division', ['cctv', 'helpdesk'])->default('helpdesk');
            $table->enum('source', ['admin', 'technician'])->default('admin');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('completion_mode', ['collective', 'individual'])
                ->default('collective')
                ->comment('collective=1 selesai semua selesai, individual=per teknisi');

            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');

            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->string('building')->nullable()->comment('Nama gedung, contoh: Gedung A');
            $table->string('location')->nullable()->comment('Ruangan/detail, contoh: Keuangan');

            $table->text('resolution_note')->nullable();
            $table->string('photo')->nullable()->comment('Path foto bukti penyelesaian');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('deadline_at')->nullable();

            $table->timestamps();
            $table->index(['status', 'created_at']);
            $table->index(['status', 'priority', 'created_at']);
            $table->index(['division', 'status']);
            $table->index('created_at');
            $table->index('completed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
