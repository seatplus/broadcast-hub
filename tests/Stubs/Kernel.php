<?php

namespace Seatplus\BroadcastHub\Tests\Stubs;

use Orchestra\Testbench\Foundation\Http\Kernel as OrchestraHttpKernel;
use Seatplus\Web\Http\Middleware\Authenticate;

class Kernel extends OrchestraHttpKernel
{
    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => Authenticate::class,
    ];
}
