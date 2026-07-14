<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_user', function (Blueprint $table) {
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('godown_user', function (Blueprint $table) {
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('module')->nullable()->after('event');
            $table->string('action')->nullable()->after('module');
            $table->nullableMorphs('auditable');
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('godown_id')->nullable()->constrained()->nullOnDelete();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->index(['module', 'action', 'created_at']);
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->foreignId('active_shop_id')->nullable()->constrained('shops')->nullOnDelete();
            $table->foreignId('active_godown_id')->nullable()->constrained('godowns')->nullOnDelete();
        });

        Schema::create('login_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('identifier')->nullable();
            $table->string('event');
            $table->string('session_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('logged_in_at')->nullable();
            $table->timestamp('logged_out_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('context_switch_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_shop_id')->nullable()->constrained('shops')->nullOnDelete();
            $table->foreignId('to_shop_id')->nullable()->constrained('shops')->nullOnDelete();
            $table->foreignId('from_godown_id')->nullable()->constrained('godowns')->nullOnDelete();
            $table->foreignId('to_godown_id')->nullable()->constrained('godowns')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('context_switch_logs');
        Schema::dropIfExists('login_histories');

        Schema::table('sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('active_shop_id');
            $table->dropConstrainedForeignId('active_godown_id');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['module', 'action', 'created_at']);
            $table->dropConstrainedForeignId('shop_id');
            $table->dropConstrainedForeignId('godown_id');
            $table->dropMorphs('auditable');
            $table->dropColumn(['module', 'action', 'old_values', 'new_values']);
        });

        Schema::table('godown_user', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['is_default', 'is_active']);
        });

        Schema::table('shop_user', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['is_default', 'is_active']);
        });
    }
};
