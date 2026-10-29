<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('gateway');
            $table->string('gateway_payment_id')->nullable();
            $table->string('method')->default('pix');
            $table->string('status')->default('pending');
            $table->string('period', 7)->nullable();
            $table->string('payer_name')->nullable();
            $table->string('payer_email')->nullable();
            $table->text('pix_payload')->nullable();
            $table->text('pix_qr_base64')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('amount_cents');
            $table->unsignedBigInteger('fee_cents')->default(0);
            $table->unsignedBigInteger('net_cents')->default(0);
            $table->unsignedBigInteger('payout_id')->nullable();
            $table->foreignId('ong_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index(['ong_id', 'status', 'period']);
            $table->index('gateway_payment_id');
            $table->index('payout_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
