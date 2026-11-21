<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('stores the email address in lowercase without surrounding spaces', function () {
    $user = User::factory()->create(['email' => '  Wariss.Bada@Example.COM ']);

    expect($user->refresh()->email)->toBe('wariss.bada@example.com');
});

it('rejects a second account whose email differs only by case', function () {
    User::factory()->create(['email' => 'admin@example.com']);

    DB::table('users')->insert([
        'name' => 'Doublon',
        'email' => 'ADMIN@example.com',
        'password' => 'hash',
    ]);
})->throws(QueryException::class, 'users_email_lower_unique');

it('rejects a role outside admin and viewer at the database level', function () {
    DB::table('users')->insert([
        'name' => 'Intrus',
        'email' => 'intrus@example.com',
        'password' => 'hash',
        'role' => 'superadmin',
    ]);
})->throws(QueryException::class, 'users_role_check');

it('creates an active viewer account by default', function () {
    $user = User::create([
        'name' => 'Nouveau',
        'email' => 'nouveau@example.com',
        'password' => 'mot-de-passe-solide',
    ]);

    expect($user->refresh())
        ->role->toBe(UserRole::Viewer)
        ->is_active->toBeTrue();
});

it('does not allow the role to be mass assigned', function () {
    $user = new User(['name' => 'Malin', 'email' => 'malin@example.com', 'role' => 'admin']);

    expect($user->role)->toBe(UserRole::Viewer);
});

it('identifies only admin accounts as admins', function () {
    $admin = User::factory()->admin()->make();
    $viewer = User::factory()->make();

    expect($admin->isAdmin())->toBeTrue()
        ->and($viewer->isAdmin())->toBeFalse();
});
