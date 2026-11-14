<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add sport storage without changing or dropping the existing sport_type column.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('sport')->nullable();
            $table->string('custom_sport', 50)->nullable();
        });

        DB::table('events')
            ->select('id', 'sport_type')
            ->whereNull('sport')
            ->orderBy('id')
            ->lazy()
            ->each(function (object $event): void {
                DB::table('events')->where('id', $event->id)->update([
                    'sport' => $event->sport_type,
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['sport', 'custom_sport']);
        });
    }
};
