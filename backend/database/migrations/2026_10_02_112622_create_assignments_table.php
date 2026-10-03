<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('tingkat'); // e.g. 7, 8, 9, 10, 11, 12
            $table->string('subject_name'); // e.g. Agama, Matematika
            $table->string('title');
            $table->text('description');
            $table->dateTime('due_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assignments');
    }
};
