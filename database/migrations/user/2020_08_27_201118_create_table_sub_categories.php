<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableSubCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('_sub_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name_conf', 500);
            $table->string('acronym_conf', 50);
            $table->string('code_type', 10);
            $table->string('type', 300);
            //$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('_sub_categories');
    }
}
