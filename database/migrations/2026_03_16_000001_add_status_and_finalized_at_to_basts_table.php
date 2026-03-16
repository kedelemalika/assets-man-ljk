<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('basts', function (Blueprint $table) {
            $table->string('status')->default('draft')->after('notes');
            $table->timestamp('finalized_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('basts', function (Blueprint $table) {
            $table->dropColumn(['status', 'finalized_at']);
        });
    }
};