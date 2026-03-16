<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bast_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bast_id');
            $table->unsignedInteger('asset_id');
            $table->string('condition_notes')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->foreign('bast_id')->references('id')->on('basts')->cascadeOnDelete();
            $table->foreign('asset_id')->references('id')->on('assets')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bast_items');
    }
};