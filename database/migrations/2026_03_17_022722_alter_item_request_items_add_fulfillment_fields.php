<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_request_items', function (Blueprint $table) {
            if (!Schema::hasColumn('item_request_items', 'fulfillment_type')) {
                $table->enum('fulfillment_type', ['existing_stock', 'procurement'])
                    ->default('existing_stock')
                    ->after('item_type');
            }

            if (!Schema::hasColumn('item_request_items', 'is_registered')) {
                $table->boolean('is_registered')
                    ->default(false)
                    ->after('consumable_id');
            }

            if (!Schema::hasColumn('item_request_items', 'is_fulfilled')) {
                $table->boolean('is_fulfilled')
                    ->default(false)
                    ->after('is_registered');
            }

            if (!Schema::hasColumn('item_request_items', 'estimated_price')) {
                $table->decimal('estimated_price', 15, 2)
                    ->nullable()
                    ->after('qty');
            }
        });
    }

    public function down(): void
    {
        Schema::table('item_request_items', function (Blueprint $table) {
            $drops = [];

            foreach ([
                'fulfillment_type',
                'is_registered',
                'is_fulfilled',
                'estimated_price',
            ] as $column) {
                if (Schema::hasColumn('item_request_items', $column)) {
                    $drops[] = $column;
                }
            }

            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};