<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Additive only: existing rows stay fixed-date events via the column default.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('schedule_type')->default('fixed');
        });

        Schema::create('event_time_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->dateTime('starts_at');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('event_time_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_time_option_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('vote');
            $table->timestamps();

            $table->unique(['event_time_option_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_time_votes');
        Schema::dropIfExists('event_time_options');

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('schedule_type');
        });
    }
};
