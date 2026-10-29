<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payout_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('amount_cents');
            $table->foreignId('payout_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['payout_id', 'payment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_items');
    }
};
