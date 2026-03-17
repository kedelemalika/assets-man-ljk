<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_request_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_request_id');

            $table->string('item_name');
            $table->text('spec')->nullable();
            $table->integer('qty')->default(1);
            $table->decimal('estimated_price', 15, 2)->nullable();

            $table->enum('item_type', ['asset', 'consumable']);

            $table->unsignedBigInteger('asset_id')->nullable();
            $table->unsignedBigInteger('consumable_id')->nullable();

            $table->timestamps();

            $table->foreign('item_request_id')
                ->references('id')
                ->on('item_requests')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_request_items');
    }
};