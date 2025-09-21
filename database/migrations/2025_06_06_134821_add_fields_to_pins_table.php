<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('pins', function (Blueprint $table) {
            $table->text('body')->nullable();
            $table->string('banner')->nullable();
            $table->boolean('moderated')->default(false);
        });
    }

    public function down()
    {
        Schema::table('pins', function (Blueprint $table) {
            $table->dropColumn(['body', 'banner', 'moderated']);
        });
    }

};
