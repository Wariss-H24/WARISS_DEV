<?php

namespace App\Notifications;

use App\Models\Communication\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Prévient le compte admin d'un nouveau message reçu par le formulaire de contact (D13).
 * « Répondre » dans la messagerie répond directement au visiteur.
 */
class NewContactMessage extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Message $message)
    {
        $this->afterCommit();
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
        $adminUrl = rtrim((string) config('wariss.admin_url'), '/')."/messages/{$this->message->id}";

        return (new MailMessage)
            ->subject('[WARISS.DEV] '.($this->message->subject ?: 'Nouveau message de '.$this->message->name))
            ->replyTo($this->message->email, $this->message->name)
            ->greeting('Nouveau message ('.$this->message->type->value.')')
            ->line("De : {$this->message->name} <{$this->message->email}>")
            ->line('Langue : '.$this->message->locale)
            ->line(Str::limit($this->message->body, 1500))
            ->action('Ouvrir dans l\'administration', $adminUrl);
    }
}
