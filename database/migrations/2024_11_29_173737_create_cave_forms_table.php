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
        Schema::create('cave_forms', function (Blueprint $table) {
            $table->id();
            $table->integer('location_id');
            $table->integer('category_id');
            $table->string('client_name');
            $table->text('remarks');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cave_forms');
    }
};
