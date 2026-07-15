<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_godown', function (Blueprint $table) {
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('godown_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['shop_id', 'godown_id']);
        });

        DB::table('godowns')
            ->whereNotNull('shop_id')
            ->orderBy('id')
            ->chunkById(250, function ($godowns): void {
                $timestamp = now();

                DB::table('shop_godown')->insertOrIgnore(
                    $godowns->map(fn ($godown) => [
                        'shop_id' => $godown->shop_id,
                        'godown_id' => $godown->id,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ])->all()
                );
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_godown');
    }
};
