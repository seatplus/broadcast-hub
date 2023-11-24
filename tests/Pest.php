<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

use Seatplus\Auth\Models\Permissions\Permission;
use Seatplus\BroadcastHub\Tests\TestCase;

uses(TestCase::class)
    ->in('Integration');

uses(TestCase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function assignPermissionToUser(Seatplus\Auth\Models\User $user, array|string $permission_strings)
{
    $permission_strings = is_array($permission_strings) ? $permission_strings : [$permission_strings];

    foreach ($permission_strings as $string) {
        $permission = Permission::findOrCreate($string);

        $user->givePermissionTo($permission);
    }

    // now re-register all the roles and permissions
    app()->make(\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();
    app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
}

function updateCharacterRoles(Seatplus\Eveapi\Models\Character\CharacterInfo $character, array $roles)
{
    $character_roles = $character->roles;

    $character_roles->roles = $roles;
    $character_roles->save();
}

function faker()
{
    return \Faker\Factory::create();
}
