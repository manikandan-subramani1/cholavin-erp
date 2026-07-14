<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reference_masters', function (Blueprint $table) {
            $table->index(['type', 'shop_id', 'is_active', 'name'], 'reference_master_listing_idx');
            $table->index(['type', 'parent_id', 'is_active'], 'reference_master_parent_idx');
        });

        Schema::table('commercial_documents', function (Blueprint $table) {
            $table->index(['shop_id', 'type', 'status', 'document_date'], 'commercial_document_listing_idx');
            $table->index(['shop_id', 'godown_id', 'type', 'document_date'], 'commercial_document_context_idx');
        });

        Schema::table('parties', function (Blueprint $table) {
            $table->index(['shop_id', 'type', 'is_active', 'name'], 'party_listing_idx');
        });
    }

    public function down(): void
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->dropIndex('party_listing_idx');
        });

        Schema::table('commercial_documents', function (Blueprint $table) {
            $table->dropIndex('commercial_document_listing_idx');
            $table->dropIndex('commercial_document_context_idx');
        });

        Schema::table('reference_masters', function (Blueprint $table) {
            $table->dropIndex('reference_master_listing_idx');
            $table->dropIndex('reference_master_parent_idx');
        });
    }
};
