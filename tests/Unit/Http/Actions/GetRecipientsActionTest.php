<?php

it('returns user recipient for non manager', function () {

    [$connector_type_encoded, $user] = prepareRecipientsTest();

    // check that user cannot manage broadcast channels
    expect($user->can('manage broadcasts channel'))->toBeFalse();

    // act as user
    \Pest\Laravel\actingAs($user);

    // call action
    $response = (new \Seatplus\BroadcastHub\Http\Actions\GetRecipientsAction())->execute($connector_type_encoded);

    // expect response to contain user recipient
    expect($response['user_recipient'])->not->toBeNull();

    // expect response to contain no recipients
    expect($response['recipients'])->toBeArray()->toBeEmpty();

});

it('returns recipients for manager', function () {

    [$connector_type_encoded, $user] = prepareRecipientsTest();

    // give user permission to manage broadcast channels
    assignPermissionToUser($user, 'manage broadcasts channel');

    // check that user can manage broadcast channels
    expect($user->can('manage broadcasts channel'))->toBeTrue();

    // create non-user recipient
    \Seatplus\BroadcastHub\Recipient::create([
        'connector_id' => '123',
        'connector_type' => base64_decode($connector_type_encoded),
        'name' => 'test',
    ]);

    // act as user
    \Pest\Laravel\actingAs($user);

    // call action
    $response = (new \Seatplus\BroadcastHub\Http\Actions\GetRecipientsAction())->execute($connector_type_encoded);

    // expect response to contain user recipient
    expect($response['user_recipient'])->not->toBeNull();

    // expect response to contain recipients
    expect($response['recipients'])->toBeCollection()->not->toBeEmpty();

});

function prepareRecipientsTest()
{
    // prepare connector type
    $connector_type_decoded = \Seatplus\Connector\Models\User::class;
    $connector_type_encoded = base64_encode($connector_type_decoded);

    // prepare user
    $user = \Illuminate\Support\Facades\Event::fakeFor(fn () => \Seatplus\Auth\Models\User::factory()->create());

    // prepare connector user
    $connector_user = \Seatplus\Connector\Models\User::create([
        'user_id' => $user->getAuthIdentifier(),
        'connector_id' => $user->getAuthIdentifier(),
        'connector_type' => $connector_type_decoded,
    ]);

    // expect user to have main character
    expect($user->main_character)->not->toBeNull();

    // expect no recipients to exist
    expect(\Seatplus\BroadcastHub\Recipient::count())->toBe(0);

    return [$connector_type_encoded, $user];
}
