<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_suite_model_prices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('provider')->index();
            $table->string('model')->index();
            $table->decimal('input_per_1m', 18, 8)->default(0);
            $table->decimal('output_per_1m', 18, 8)->default(0);
            $table->decimal('cached_input_per_1m', 18, 8)->nullable();
            $table->unsignedBigInteger('unit')->default(1000000);
            $table->string('currency', 8)->default('USD');
            $table->string('source')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'model']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_suite_model_prices');
    }
};