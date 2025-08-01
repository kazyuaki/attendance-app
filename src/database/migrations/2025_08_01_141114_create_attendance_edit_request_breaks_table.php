<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceEditRequestBreaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_edit_request_breaks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_edit_request_id');
            $table->timestamp('break_start')->nullable();
            $table->timestamp('break_end')->nullable();
            $table->timestamps();

            // 外部キー制約に短い名前を付ける（ここがポイント！）
            $table->foreign('attendance_edit_request_id', 'aerb_foreign')
                ->references('id')
                ->on('attendance_edit_requests')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_edit_request_breaks');
    }
}
