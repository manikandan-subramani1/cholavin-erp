<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commercial_documents', function (Blueprint $table): void {
            $table->index(['financial_year_id', 'shop_id', 'godown_id', 'status', 'document_date', 'type'], 'documents_dashboard_scope_idx');
            $table->index(['financial_year_id', 'shop_id', 'status', 'due_date', 'type'], 'documents_dashboard_due_idx');
        });
        Schema::table('payments', function (Blueprint $table): void {
            $table->index(['financial_year_id', 'shop_id', 'godown_id', 'payment_date', 'type'], 'payments_dashboard_scope_idx');
        });
        Schema::table('stock_transfers', function (Blueprint $table): void {
            $table->index(['financial_year_id', 'shop_id', 'status', 'transfer_date'], 'transfers_dashboard_scope_idx');
        });
        Schema::table('deliveries', function (Blueprint $table): void {
            $table->index(['shop_id', 'status', 'scheduled_at'], 'deliveries_dashboard_scope_idx');
        });
        Schema::table('activity_logs', function (Blueprint $table): void {
            $table->index(['financial_year_id', 'shop_id', 'godown_id', 'created_at'], 'activity_dashboard_scope_idx');
        });
        Schema::table('user_notifications', function (Blueprint $table): void {
            $table->index(['user_id', 'shop_id', 'read_at', 'created_at'], 'notifications_dashboard_scope_idx');
        });
    }

    public function down(): void
    {
        Schema::table('user_notifications', fn (Blueprint $table) => $table->dropIndex('notifications_dashboard_scope_idx'));
        Schema::table('activity_logs', fn (Blueprint $table) => $table->dropIndex('activity_dashboard_scope_idx'));
        Schema::table('deliveries', fn (Blueprint $table) => $table->dropIndex('deliveries_dashboard_scope_idx'));
        Schema::table('stock_transfers', fn (Blueprint $table) => $table->dropIndex('transfers_dashboard_scope_idx'));
        Schema::table('payments', fn (Blueprint $table) => $table->dropIndex('payments_dashboard_scope_idx'));
        Schema::table('commercial_documents', function (Blueprint $table): void {
            $table->dropIndex('documents_dashboard_scope_idx');
            $table->dropIndex('documents_dashboard_due_idx');
        });
    }
};
