<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_request_approvals', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_approver_id')->nullable()->after('approval_order');
            $table->string('assigned_role')->nullable()->after('assigned_approver_id');
        });
    }

    public function down(): void
    {
        Schema::table('item_request_approvals', function (Blueprint $table) {
            $table->dropColumn([
                'assigned_approver_id',
                'assigned_role',
            ]);
        });
    }
};
