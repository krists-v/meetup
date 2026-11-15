<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_user', function (Blueprint $table) {
            $table->string('rsvp_status', 32)->default('going');
        });

        foreach (DB::table('event_user')->get() as $row) {
            DB::table('event_user')->where('id', $row->id)->update([
                'rsvp_status' => $row->status,
            ]);
        }

        Schema::table('event_user', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('event_user', function (Blueprint $table) {
            $table->renameColumn('rsvp_status', 'status');
        });
    }

    public function down(): void
    {
        Schema::table('event_user', function (Blueprint $table) {
            $table->string('rsvp_status', 32)->default('going');
        });

        foreach (DB::table('event_user')->get() as $row) {
            DB::table('event_user')->where('id', $row->id)->update([
                'rsvp_status' => in_array($row->status, ['going', 'maybe', 'not_going', 'invited'], true)
                    ? $row->status
                    : 'maybe',
            ]);
        }

        Schema::table('event_user', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('event_user', function (Blueprint $table) {
            $table->renameColumn('rsvp_status', 'status');
        });
    }
};
