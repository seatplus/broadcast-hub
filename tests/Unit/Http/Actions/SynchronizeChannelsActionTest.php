<?php

use Seatplus\BroadcastHub\Recipient;

it('synchronizes recipients with checked channels', function () {

    // given
    $broadcaster = 'eveonline';
    $encoded_broadcaster = base64_encode($broadcaster);

    $checked_channels = [
        [
            'id' => 1,
            'name' => 'channel1',
        ],
        [
            'id' => 2,
            'name' => 'channel2',
        ],
    ];

    // create a recipient without a name, as it belongs to an individual user
    $user_recipient = Recipient::factory()->create([
        'connector_type' => $broadcaster,
        'name' => null,
    ]);

    // create a recipient with a name, as it belongs to a channel
    $channel_to_be_deleted = Recipient::factory()->create([
        'connector_type' => $broadcaster,
        'name' => 'channel3',
    ]);

    // expect two recipients to be created
    expect(Recipient::count())->toBe(2);

    // run the action
    $action = new \Seatplus\BroadcastHub\Http\Actions\SynchronizeChannelsAction();
    $action->execute($encoded_broadcaster, $checked_channels);

    // expect the cahnnel to be deleted not to exist anymore
    expect(Recipient::find($channel_to_be_deleted->id))->toBeNull();

    // expect to have three recipients now
    expect(Recipient::count())->toBe(3);

    // expect the user recipient to still exist
    expect(Recipient::find($user_recipient->id))->not->toBeNull();

    // expect to have the two checked channels
    expect(Recipient::whereIn('name', ['channel1', 'channel2'])->count())->toBe(2);

});
