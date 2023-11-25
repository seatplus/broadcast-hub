<?php

it('can broadcast all', function () {

    $this->artisan('broadcast-hub:all')
        ->expectsOutput('Broadcasting all messages')
        ->assertExitCode(0);

});
