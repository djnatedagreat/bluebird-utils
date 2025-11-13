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
        Schema::create('paths', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('core_upgrade_id');
            $table->string('path');
            $table->enum('type',['custom','core']);
            // safe = marked safe for the upgraded. Doesn't need attention
            // override = The file overrides a core file and therefore might
            // need to be checked against that file for conflicts
            // attention = file needs attention of some sort.
            // conflict = conflict found -- stronger warning that something needs attention
            // patched = patch has been applied.
            $table->set('flags',['safe','override','attention','conflict'])->nullable();
            $table->boolean('complete');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->foreign('core_upgrade_id')->references('id')->on('core_upgrades');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paths');
    }
};
