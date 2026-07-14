<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('variant_id')->nullable()->after('brand_id')->constrained('reference_masters')->nullOnDelete();
            $table->foreignId('grade_id')->nullable()->after('variant_id')->constrained('reference_masters')->nullOnDelete();
        });

        Schema::table('commercial_documents', function (Blueprint $table) {
            $table->decimal('expense_amount', 16, 2)->default(0)->after('tax_amount');
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->string('source_type', 50)->nullable()->after('reference_number');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            $table->index(['source_type', 'source_id']);
        });

        Schema::table('user_notifications', function (Blueprint $table) {
            $table->string('channel', 20)->default('in_app')->after('type');
            $table->string('recipient')->nullable()->after('channel');
            $table->timestamp('sent_at')->nullable()->after('read_at');
            $table->timestamp('failed_at')->nullable()->after('sent_at');
            $table->text('failure_reason')->nullable()->after('failed_at');
        });
    }

    public function down(): void
    {
        Schema::table('user_notifications', function (Blueprint $table) {
            $table->dropColumn(['channel', 'recipient', 'sent_at', 'failed_at', 'failure_reason']);
        });
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropIndex(['source_type', 'source_id']);
            $table->dropColumn(['source_type', 'source_id']);
        });
        Schema::table('commercial_documents', fn (Blueprint $table) => $table->dropColumn('expense_amount'));
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('variant_id');
            $table->dropConstrainedForeignId('grade_id');
        });
    }
};
