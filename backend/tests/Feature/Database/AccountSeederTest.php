<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\AccountSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    config([
        'wariss.seed_accounts.admin' => ['name' => 'Admin', 'email' => 'Admin@Wariss.test', 'password' => 'admin-password-123'],
        'wariss.seed_accounts.viewer' => ['name' => 'Lecteur', 'email' => 'viewer@wariss.test', 'password' => 'viewer-password-123'],
    ]);
});

it('creates the admin and viewer accounts with their roles', function () {
    $this->seed(AccountSeeder::class);

    $admin = User::where('email', 'admin@wariss.test')->sole();
    $viewer = User::where('email', 'viewer@wariss.test')->sole();

    expect($admin->role)->toBe(UserRole::Admin)
        ->and(Hash::check('admin-password-123', $admin->password))->toBeTrue()
        ->and($viewer->role)->toBe(UserRole::Viewer)
        ->and(Hash::check('viewer-password-123', $viewer->password))->toBeTrue();
});

it('keeps the existing password when it runs a second time', function () {
    $this->seed(AccountSeeder::class);
    config(['wariss.seed_accounts.admin.password' => 'autre-mot-de-passe']);

    $this->seed(AccountSeeder::class);

    $admin = User::where('email', 'admin@wariss.test')->sole();
    expect(User::count())->toBe(2)
        ->and(Hash::check('admin-password-123', $admin->password))->toBeTrue();
});

it('generates a password outside production when none is configured', function () {
    config(['wariss.seed_accounts.viewer.password' => null]);

    $this->seed(AccountSeeder::class);

    expect(User::where('email', 'viewer@wariss.test')->exists())->toBeTrue();
});

it('refuses to create an account without a configured password in production', function () {
    config(['wariss.seed_accounts.admin.password' => null]);
    $this->app['env'] = 'production';

    $this->app->make(AccountSeeder::class)->run();
})->throws(RuntimeException::class, 'SEED_ADMIN_PASSWORD');
