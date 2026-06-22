<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sync_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('client_id', 64);
            $table->string('method', 10);
            $table->string('url', 500);
            $table->json('payload');
            $table->bigInteger('client_timestamp');
            $table->string('status', 20)->default('pending');
            $table->json('server_response')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'client_id']);
            $table->index(['user_id', 'status']);
            $table->index('client_timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_requests');
    }
};
