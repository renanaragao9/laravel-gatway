<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->string('period', 7);
            $table->string('status')->default('pending');
            $table->string('gateway')->nullable();
            $table->string('gateway_transfer_id')->nullable();
            $table->string('receipt_path')->nullable();
            $table->timestamp('scheduled_for')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('gross_cents')->default(0);
            $table->unsignedBigInteger('fee_cents')->default(0);
            $table->unsignedBigInteger('net_cents')->default(0);
            $table->foreignId('ong_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['ong_id', 'period']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
