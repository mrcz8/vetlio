<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->timestamp('date');
            $table->timestamp('from');
            $table->timestamp('to');
            $table->integer('client_id');
            $table->integer('patient_id');
            $table->smallInteger('status_id');
            $table->unsignedInteger('branch_id');
            $table->string('note')->nullable();
            $table->integer('service_provider_id');
            $table->integer('user_id');
            $table->string('reason_for_coming')->nullable();
            $table->integer('room_id');
            $table->integer('service_id');
            $table->timestamp('canceled_at')->nullable();
            $table->unsignedInteger('cancel_reason_id')->nullable();
            $table->timestamp('waiting_room_at')->nullable();
            $table->timestamp('in_process_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('confirmed_status_id')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->string('confirmed_note')->nullable();
            $table->integer('organisation_id');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
