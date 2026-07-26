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
            $table->foreignId('variant_id')->nullable()->after('brand_id')->constrained('reference_masters')->nullOnDelete();
            $table->foreignId('grade_id')->nullable()->after('variant_id')->constrained('reference_masters')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->after('grade_id')->constrained('reference_masters')->nullOnDelete();
            $table->foreignId('tax_rate_id')->nullable()->after('unit_id')->constrained('reference_masters')->nullOnDelete();
            $table->foreignId('hsn_sac_id')->nullable()->after('tax_rate_id')->constrained('reference_masters')->nullOnDelete();
            $table->decimal('purchase_price', 14, 2)->default(0)->after('price');
            $table->decimal('sale_price', 14, 2)->default(0)->after('purchase_price');
            $table->decimal('opening_stock', 14, 3)->default(0)->after('sale_price');
            $table->decimal('reorder_level', 14, 3)->default(0)->after('opening_stock');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('hsn_sac_id');
            $table->dropConstrainedForeignId('tax_rate_id');
            $table->dropConstrainedForeignId('unit_id');
            $table->dropConstrainedForeignId('grade_id');
            $table->dropConstrainedForeignId('variant_id');
            $table->dropConstrainedForeignId('brand_id');
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn(['sku', 'barcode', 'purchase_price', 'sale_price', 'opening_stock', 'reorder_level']);
        });

        Schema::dropIfExists('reference_masters');
    }
};
