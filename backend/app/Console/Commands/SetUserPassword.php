<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/**
 * Définit le mot de passe d'un compte d'administration.
 *
 * Le mot de passe est saisi au clavier sans être affiché, confirmé, puis validé par la
 * politique de l'application. Il n'est jamais écrit dans un fichier ni dans l'historique.
 * Les sessions ouvertes du compte sont fermées.
 */
#[Signature('app:set-password {email : Adresse e-mail du compte}')]
#[Description("Définit le mot de passe d'un compte d'administration (saisie masquée)")]
class SetUserPassword extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));
        $user = User::where('email', $email)->first();

        if ($user === null) {
            $this->error("Aucun compte avec l'adresse {$email}.");

            return self::FAILURE;
        }

        $password = (string) $this->secret('Nouveau mot de passe');
        $confirmation = (string) $this->secret('Confirmez le mot de passe');

        $validator = Validator::make(
            ['password' => $password, 'password_confirmation' => $confirmation],
            ['password' => ['required', 'confirmed', Password::defaults()]],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $user->password = $password;
        $user->setRememberToken(Str::random(60));
        $user->save();

        DB::table('sessions')->where('user_id', $user->id)->delete();

        $this->info("Mot de passe mis à jour pour {$email}. Les sessions ouvertes de ce compte ont été fermées.");

        return self::SUCCESS;
    }
}
