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
        Schema::create('org_structure_trees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('org_structure_id')->nullable();
            $table->unsignedBiginteger('user_id')->nullable();
            $table->bigInteger('reports_to_id')->nullable();
            $table->string('title')->nullable();
            $table->timestamps();

            $table->foreign('org_structure_id')
                ->references('id')->on('org_structures')
                ->onDelete('cascade');

            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_structure_trees');
    }
};
