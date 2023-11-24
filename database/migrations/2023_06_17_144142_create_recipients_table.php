<?php

use Illuminate\Support\Facades\Schema;

return new class extends \Illuminate\Database\Migrations\Migration
{
    public function up()
    {
        Schema::create('recipients', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('connector_id')->unique();
            $table->string('connector_type')->index();
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }
};
