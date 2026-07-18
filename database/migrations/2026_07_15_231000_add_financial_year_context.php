<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_year_user', function (Blueprint $table): void {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('financial_year_id')->constrained('reference_masters')->cascadeOnDelete();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->primary(['user_id', 'financial_year_id']);
        });

        Schema::table('sessions', function (Blueprint $table): void {
            $table->foreignId('active_financial_year_id')->nullable()->after('active_godown_id')
                ->constrained('reference_masters')->nullOnDelete();
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->foreignId('financial_year_id')->nullable()->after('godown_id')
                ->constrained('reference_masters')->nullOnDelete();
        });

        Schema::table('stock_transfers', function (Blueprint $table): void {
            $table->foreignId('financial_year_id')->nullable()->after('shop_id')
                ->constrained('reference_masters')->nullOnDelete();
        });

        Schema::table('activity_logs', function (Blueprint $table): void {
            $table->foreignId('financial_year_id')->nullable()->after('godown_id')
                ->constrained('reference_masters')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', fn (Blueprint $table) => $table->dropConstrainedForeignId('financial_year_id'));
        Schema::table('stock_transfers', fn (Blueprint $table) => $table->dropConstrainedForeignId('financial_year_id'));
        Schema::table('payments', fn (Blueprint $table) => $table->dropConstrainedForeignId('financial_year_id'));
        Schema::table('sessions', fn (Blueprint $table) => $table->dropConstrainedForeignId('active_financial_year_id'));
        Schema::dropIfExists('financial_year_user');
    }
};
