<?php

use Illuminate\Support\Facades\Schema;

return new class extends \Illuminate\Database\Migrations\Migration
{
    public function up()
    {
        Schema::create('subscriptions', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUlid('recipient_id')->constrained('recipients');
            $table->string('notification')->index();
            $table->morphs('subscribable');
            $table->timestamps();
        });
    }
};
