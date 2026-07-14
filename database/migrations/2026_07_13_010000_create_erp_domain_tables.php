<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reference_masters', function (Blueprint $table) {
            $table->id();
            $table->string('type', 60)->index();
            $table->foreignId('parent_id')->nullable()->constrained('reference_masters')->nullOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code', 80);
            $table->text('description')->nullable();
            $table->decimal('percentage', 8, 4)->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['type', 'code']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('sku')->nullable()->unique()->after('slug');
            $table->string('barcode')->nullable()->unique()->after('sku');
            $table->foreignId('category_id')->nullable()->after('barcode')->constrained('reference_masters')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->after('category_id')->constrained('reference_masters')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->after('brand_id')->constrained('reference_masters')->nullOnDelete();
            $table->foreignId('tax_rate_id')->nullable()->after('unit_id')->constrained('reference_masters')->nullOnDelete();
            $table->foreignId('hsn_sac_id')->nullable()->after('tax_rate_id')->constrained('reference_masters')->nullOnDelete();
            $table->decimal('purchase_price', 14, 2)->default(0)->after('price');
            $table->decimal('sale_price', 14, 2)->default(0)->after('purchase_price');
            $table->decimal('opening_stock', 14, 3)->default(0)->after('sale_price');
            $table->decimal('reorder_level', 14, 3)->default(0)->after('opening_stock');
        });

        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->restrictOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('reference_masters')->nullOnDelete();
            $table->enum('type', ['customer', 'supplier', 'both'])->index();
            $table->string('code', 60);
            $table->string('name');
            $table->string('mobile', 30)->nullable()->index();
            $table->string('email')->nullable();
            $table->string('gstin', 30)->nullable();
            $table->string('pan', 20)->nullable();
            $table->decimal('credit_limit', 14, 2)->default(0);
            $table->decimal('opening_balance', 14, 2)->default(0);
            $table->enum('balance_type', ['receivable', 'payable'])->default('receivable');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['shop_id', 'code']);
        });

        Schema::create('party_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('party_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['billing', 'shipping', 'other'])->default('billing');
            $table->string('contact_name')->nullable();
            $table->string('mobile', 30)->nullable();
            $table->text('address');
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country')->default('India');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('commercial_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->restrictOnDelete();
            $table->foreignId('godown_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('financial_year_id')->nullable()->constrained('reference_masters')->nullOnDelete();
            $table->foreignId('party_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('type', 40)->index();
            $table->string('number', 60);
            $table->date('document_date')->index();
            $table->date('due_date')->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->string('reference_number')->nullable();
            $table->decimal('subtotal', 16, 2)->default(0);
            $table->decimal('discount_amount', 16, 2)->default(0);
            $table->decimal('tax_amount', 16, 2)->default(0);
            $table->decimal('round_off', 10, 2)->default(0);
            $table->decimal('total_amount', 16, 2)->default(0);
            $table->decimal('paid_amount', 16, 2)->default(0);
            $table->decimal('balance_amount', 16, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['shop_id', 'type', 'number']);
        });

        Schema::create('commercial_document_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commercial_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('description')->nullable();
            $table->decimal('quantity', 14, 3);
            $table->string('unit', 40)->nullable();
            $table->decimal('rate', 14, 2);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('tax_rate', 8, 4)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('line_total', 16, 2);
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('godown_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number')->default('');
            $table->date('expiry_date')->nullable();
            $table->decimal('quantity', 16, 3)->default(0);
            $table->decimal('average_cost', 16, 4)->default(0);
            $table->timestamps();
            $table->unique(['shop_id', 'godown_id', 'product_id', 'batch_number'], 'inventory_balance_unique');
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->restrictOnDelete();
            $table->foreignId('godown_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('commercial_document_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 40)->index();
            $table->date('movement_date')->index();
            $table->string('reference_number')->nullable();
            $table->string('batch_number')->default('');
            $table->date('expiry_date')->nullable();
            $table->decimal('quantity', 16, 3);
            $table->decimal('rate', 16, 4)->default(0);
            $table->decimal('value', 16, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->index(['shop_id', 'godown_id', 'product_id', 'movement_date'], 'inventory_movement_context');
        });

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->restrictOnDelete();
            $table->foreignId('from_godown_id')->constrained('godowns')->restrictOnDelete();
            $table->foreignId('to_godown_id')->constrained('godowns')->restrictOnDelete();
            $table->string('number', 60);
            $table->date('transfer_date');
            $table->string('status', 30)->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['shop_id', 'number']);
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 14, 3);
            $table->string('batch_number')->default('');
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->restrictOnDelete();
            $table->foreignId('godown_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('party_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('commercial_document_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained('reference_masters')->nullOnDelete();
            $table->string('type', 30)->index();
            $table->string('number', 60);
            $table->date('payment_date')->index();
            $table->decimal('amount', 16, 2);
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['shop_id', 'type', 'number']);
        });

        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('ledger_accounts')->nullOnDelete();
            $table->string('type', 30)->index();
            $table->string('code', 60);
            $table->string('name');
            $table->decimal('opening_balance', 16, 2)->default(0);
            $table->enum('balance_type', ['debit', 'credit'])->default('debit');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['shop_id', 'code']);
        });

        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->restrictOnDelete();
            $table->foreignId('financial_year_id')->nullable()->constrained('reference_masters')->nullOnDelete();
            $table->string('type', 30)->index();
            $table->string('number', 60);
            $table->date('voucher_date')->index();
            $table->string('reference_number')->nullable();
            $table->text('narration')->nullable();
            $table->decimal('total_debit', 16, 2)->default(0);
            $table->decimal('total_credit', 16, 2)->default(0);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['shop_id', 'type', 'number']);
        });

        Schema::create('voucher_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ledger_account_id')->constrained()->restrictOnDelete();
            $table->foreignId('party_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('debit', 16, 2)->default(0);
            $table->decimal('credit', 16, 2)->default(0);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->restrictOnDelete();
            $table->foreignId('commercial_document_id')->constrained()->restrictOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('reference_masters')->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('reference_masters')->nullOnDelete();
            $table->foreignId('route_id')->nullable()->constrained('reference_masters')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('pending')->index();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('proof_path')->nullable();
            $table->decimal('cash_collected', 16, 2)->default(0);
            $table->decimal('delivery_expense', 16, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type', 50)->index();
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('voucher_lines');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('ledger_accounts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('inventory_balances');
        Schema::dropIfExists('commercial_document_items');
        Schema::dropIfExists('commercial_documents');
        Schema::dropIfExists('party_addresses');
        Schema::dropIfExists('parties');

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropConstrainedForeignId('brand_id');
            $table->dropConstrainedForeignId('unit_id');
            $table->dropConstrainedForeignId('tax_rate_id');
            $table->dropConstrainedForeignId('hsn_sac_id');
            $table->dropColumn(['sku', 'barcode', 'purchase_price', 'sale_price', 'opening_stock', 'reorder_level']);
        });

        Schema::dropIfExists('reference_masters');
    }
};
