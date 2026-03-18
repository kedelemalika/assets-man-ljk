<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();

            $table->enum('request_type', ['asset', 'consumable']);
            $table->enum('procurement_type', ['cash', 'po'])->nullable();

            $table->unsignedBigInteger('requester_id');
            $table->unsignedBigInteger('department_id')->nullable();

            $table->text('purpose')->nullable();
            $table->decimal('estimated_total', 15, 2)->nullable();

            $table->enum('status', [
                'draft',
                'submitted',
                'approved',
                'rejected',
                'ready_for_handover',
                'handed_over'
            ])->default('draft');

            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();

            $table->timestamp('rejected_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->unsignedBigInteger('bast_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_requests');
    }
};