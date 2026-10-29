<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gateway_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('mode')->default('sandbox');
            $table->text('credentials')->nullable();
            $table->boolean('active')->default(false);
            $table->timestamps();

            $table->unique(['provider', 'mode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateway_accounts');
    }
};
