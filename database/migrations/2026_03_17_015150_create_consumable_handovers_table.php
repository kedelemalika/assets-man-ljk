<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumable_handovers', function (Blueprint $table) {
            $table->id();

            $table->string('document_number')->nullable()->unique();
            $table->date('handover_date');

            $table->unsignedBigInteger('item_request_id')->nullable();

            $table->string('handover_by');
            $table->string('received_by');

            $table->string('department')->nullable();
            $table->text('notes')->nullable();

            $table->enum('status', ['draft', 'final'])->default('draft');
            $table->timestamp('finalized_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumable_handovers');
    }
};