<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Crée les comptes admin et viewer (décision D-04).
 *
 * Idempotent : un compte existant (même e-mail) n'est jamais modifié, son mot de passe est conservé.
 */
class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /** @var array<string, array{name: string, email: string, password: ?string}> $accounts */
        $accounts = config('wariss.seed_accounts');

        foreach ($accounts as $role => $account) {
            $this->seedAccount(UserRole::from($role), $account);
        }
    }

    /**
     * @param  array{name: string, email: string, password: ?string}  $account
     */
    private function seedAccount(UserRole $role, array $account): void
    {
        $email = mb_strtolower(trim($account['email']));

        if (User::where('email', $email)->exists()) {
            $this->command?->info("Compte {$role->value} déjà présent : {$email}");

            return;
        }

        $password = $account['password'] ?? $this->generatePassword($role);

        $user = new User([
            'name' => $account['name'],
            'email' => $email,
            'password' => $password,
        ]);
        $user->role = $role;
        $user->email_verified_at = now();
        $user->save();

        $this->command?->info("Compte {$role->value} créé : {$email}");
    }

    /**
     * Génère un mot de passe en local uniquement, et l'affiche une seule fois.
     */
    private function generatePassword(UserRole $role): string
    {
        if (app()->isProduction()) {
            throw new RuntimeException('SEED_'.strtoupper($role->value).'_PASSWORD doit être défini en production.');
        }

        $password = Str::password(20);

        $this->command?->warn("Mot de passe généré pour {$role->value} (affiché une seule fois) : {$password}");

        return $password;
    }
}
