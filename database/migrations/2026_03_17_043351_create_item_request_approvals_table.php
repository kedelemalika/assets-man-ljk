<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_request_approvals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('item_request_id')
                ->constrained('item_requests')
                ->cascadeOnDelete();

            $table->integer('approval_order')->default(1);

            $table->unsignedBigInteger('approver_id')->nullable();
            $table->string('approver_name')->nullable();
            $table->string('approver_role')->nullable();

            $table->enum('status', ['waiting', 'approved', 'rejected', 'revision_needed'])
                ->default('waiting');

            $table->text('remarks')->nullable();
            $table->timestamp('acted_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_request_approvals');
    }
};