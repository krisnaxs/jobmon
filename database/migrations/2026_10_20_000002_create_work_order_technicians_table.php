<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_order_technicians', function (Blueprint $table) {
            $table->id();

            $table->foreignId('work_order_id')
                ->constrained('work_orders')
                ->onDelete('cascade');

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('team_id')
                ->nullable()
                ->constrained('teams')
                ->nullOnDelete();

            $table->string('team_name_snapshot', 50)
                ->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed'])
                ->default('pending')
                ->comment('Status pengerjaan oleh teknisi ini');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('resolution_note')->nullable()
                ->comment('Catatan penyelesaian dari teknisi ini');
            $table->boolean('no_participation')
                ->default(false)
                ->comment('True jika teknisi tidak berpartisipasi saat WO ditutup');

            $table->timestamp('skipped_at')
                ->nullable()
                ->comment('Kapan teknisi ini di-flag tidak partisipasi');

            $table->foreignId('skipped_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Admin yang flag tidak partisipasi');

            $table->foreignId('assigned_by')->nullable()
                ->constrained('users')->onDelete('set null');

            $table->timestamp('assigned_at')->useCurrent();

            $table->unique(['work_order_id', 'user_id']);
            $table->index('user_id');
            $table->index(['user_id', 'status']);
            $table->index('no_participation');
            $table->index(['work_order_id', 'no_participation']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_technicians');
    }
};
