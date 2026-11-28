<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Lien de confirmation de l'inscription à la newsletter (double opt-in, D13), dans la langue du
 * visiteur. Le jeton n'est connu que de ce message : la base n'en garde que l'empreinte.
 */
class ConfirmNewsletterSubscription extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $token, private readonly string $subscriberLocale)
    {
        $this->afterCommit();
        $this->locale($subscriberLocale);
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = config('wariss.site_url')."/{$this->subscriberLocale}/newsletter/confirm?token={$this->token}";

        return (new MailMessage)
            ->subject(__('Confirmez votre inscription à la newsletter'))
            ->greeting(__('Bonjour,'))
            ->line(__('Merci de votre intérêt pour la newsletter de WARISS.DEV. Pour confirmer votre inscription, cliquez sur le bouton ci-dessous.'))
            ->action(__('Confirmer mon inscription'), $url)
            ->line(__('Ce lien est valable 7 jours. Si vous n\'êtes pas à l\'origine de cette demande, ignorez simplement cet e-mail.'));
    }
}
