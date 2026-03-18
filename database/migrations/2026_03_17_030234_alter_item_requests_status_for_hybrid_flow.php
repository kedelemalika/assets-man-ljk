<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE item_requests 
            MODIFY status ENUM(
                'draft',
                'submitted',
                'approved',
                'procurement_process',
                'delivered',
                'ready_for_handover',
                'handed_over',
                'closed',
                'rejected',
                'revision_needed'
            ) NOT NULL DEFAULT 'draft'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE item_requests 
            MODIFY status ENUM(
                'draft',
                'submitted',
                'approved',
                'rejected',
                'ready_for_handover',
                'handed_over'
            ) NOT NULL DEFAULT 'draft'
        ");
    }
};