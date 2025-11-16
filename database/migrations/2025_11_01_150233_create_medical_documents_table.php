<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medical_documents', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->string('code')->nullable();
            $table->string('sequence')->nullable();
            $table->unsignedInteger('branch_id');
            $table->unsignedInteger('reservation_id')->nullable();
            $table->unsignedInteger('price_list_id');
            $table->unsignedInteger('patient_id');
            $table->unsignedInteger('client_id')->nullable();;
            $table->text('content')->nullable();
            $table->string('reason_for_coming')->nullable();
            $table->unsignedInteger('service_provider_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('locked_user_id')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->text('diagnosis')->nullable();
            $table->unsignedInteger('organisation_id');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_documents');
    }
};
