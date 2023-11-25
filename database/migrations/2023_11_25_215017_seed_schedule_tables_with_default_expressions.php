<?php

use Illuminate\Database\Migrations\Migration;
use Seatplus\Eveapi\Models\Schedules;

return new class extends Migration
{
    public function up(): void
    {

        $jobs = [
            // run BroadcastAllCommand every 15 minutes
            \Seatplus\BroadcastHub\Commands\BroadcastAllCommand::class => '*/15 * * * *',
        ];

        // if the schedule is not in the database, create it
        foreach ($jobs as $job => $schedule) {
            Schedules::query()->firstOrCreate([
                'job' => $job,
            ], [
                'expression' => $schedule,
            ]);
        }
    }
};
