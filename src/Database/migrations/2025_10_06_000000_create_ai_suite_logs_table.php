<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_suite_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('provider')->nullable()->index();
            $table->string('driver')->nullable();
            $table->string('model')->nullable()->index();
            $table->string('operation')->index();
            $table->string('status')->index();
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('request_id')->nullable()->index();
            $table->string('conversation_id')->nullable()->index();
            $table->string('ip')->nullable();
            $table->unsignedBigInteger('duration_ms')->default(0)->index();
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->decimal('cost', 18, 8)->default(0);
            $table->string('currency', 8)->default('USD');
            $table->longText('request_payload')->nullable();
            $table->longText('response_payload')->nullable();
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('finished_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_suite_logs');
    }
};