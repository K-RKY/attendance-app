<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->date('date');  // 勤務日
            $table->dateTime('clock_in')->nullable();
            $table->dateTime('clock_out')->nullable();

            $table->text('remarks')->nullable();

            $table->tinyInteger('status')->default(0);
            // 0: 勤務外, 1: 出勤中, 2: 休憩中, 3: 退勤済

            $table->unique(['user_id', 'date']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
