<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->decimal('wholesale_price', 14, 2)->default(0)->after('sale_price');
            $table->decimal('retail_price', 14, 2)->default(0)->after('wholesale_price');
            $table->index(['is_active', 'category_id'], 'products_active_category_idx');
            $table->index(['brand_id', 'variant_id', 'grade_id', 'unit_id'], 'products_master_filter_idx');
        });

        Schema::create('product_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('alt_text')->nullable();
            $table->boolean('is_primary')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['product_id', 'sort_order']);
        });

        Schema::create('product_price_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('purchase_price', 14, 2)->default(0);
            $table->decimal('sale_price', 14, 2)->default(0);
            $table->decimal('wholesale_price', 14, 2)->default(0);
            $table->decimal('retail_price', 14, 2)->default(0);
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_price_histories');
        Schema::dropIfExists('product_images');
        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex('products_active_category_idx');
            $table->dropIndex('products_master_filter_idx');
            $table->dropColumn(['wholesale_price', 'retail_price']);
        });
    }
};
