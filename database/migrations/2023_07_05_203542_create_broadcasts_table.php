<?php

use Illuminate\Support\Facades\Schema;

return new class extends \Illuminate\Database\Migrations\Migration
{
    public function up()
    {
        Schema::create('broadcasts', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('notification')->index();
            $table->morphs('entity');
            $table->string('payload_hash')->index();
            $table->json('payload');
            $table->timestamps();
        });
    }
};
