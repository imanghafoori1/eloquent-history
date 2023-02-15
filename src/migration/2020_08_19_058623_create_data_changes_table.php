<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataChangesTable extends Migration
{
    public function up()
    {
        Schema::create('data_changes', function (Blueprint $table) {
            $table->id();
            $table->string('col_name', 40);
            $table->text('value')->nullable();
            $table->unsignedBigInteger('change_id');
        });

        Schema::create('data_changes_meta', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('row_id');
            $table->string('table_name', 40);
            $table->ipAddress('ip');
            $table->string('route', 110);
            $table->unsignedBigInteger('user_id');
            $table->timestamp('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('data_changes_meta');
        Schema::dropIfExists('data_changes');
    }
}
