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
        Schema::create('core_upgrade_test_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('core_upgrade_id');
            $table->string('user_story_id');
            $table->string('user_story_title');
            $table->string('redmine_issue')->nullable();
            $table->enum('risk_level',['High','Normal','Low'])->default('Normal');
            $table->text('note')->nullable();
            $table->boolean('complete')->default(false);
            $table->timestamps();
            $table->foreign('core_upgrade_id')->references('id')->on('core_upgrades');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('core_upgrade_test_cases');
    }
};
