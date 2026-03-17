<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumable_handover_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('consumable_handover_id')
                ->constrained('consumable_handovers')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('consumable_id')->nullable();
            $table->string('item_name');
            $table->integer('qty')->default(1);
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumable_handover_items');
    }
};