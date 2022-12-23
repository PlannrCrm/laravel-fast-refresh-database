<?php

namespace Plannr\Laravel\FastRefreshDatabase\Tests\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
