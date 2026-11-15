<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add indoor or outdoor and skill level without rewriting sport_type.
     * Existing rows default outdoors, then indoor activities are filled in.
     * Level stays empty, which means all levels.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('setting', 16)->nullable();
            $table->string('level', 16)->nullable();
        });

        DB::table('events')->whereNull('setting')->update(['setting' => 'outdoor']);

        $indoor = ['padel', 'basketball', 'hockey', 'volleyball'];

        DB::table('events')->whereIn('sport', $indoor)->update(['setting' => 'indoor']);

        DB::table('events')
            ->where(function ($query) {
                $query->whereNull('sport')->orWhere('sport', '');
            })
            ->whereIn('sport_type', $indoor)
            ->update(['setting' => 'indoor']);
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['setting', 'level']);
        });
    }
};
