<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceEditRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_edit_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->dateTime('edited_clock_in')->nullable();
            $table->dateTime('edited_clock_out')->nullable();
            $table->text('edited_note')->nullable();
            $table->string('status')->default('pending'); // or enum if使うなら
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
        Schema::dropIfExists('attendance_edit_requests');
    }
}
