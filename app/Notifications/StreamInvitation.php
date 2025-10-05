<?php

namespace App\Notifications;

use App\Models\Stream;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StreamInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    public $stream;
    public $host;

    /**
     * Create a new notification instance.
     */
    public function __construct(Stream $stream, User $host)
    {
        $this->stream = $stream;
        $this->host = $host;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $streamUrl = url('/streams/' . $this->stream->id);

        return (new MailMessage)
            ->subject('You\'re invited to join a live stream!')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($this->host->name . ' has invited you to join their live stream.')
            ->line('**Stream:** ' . $this->stream->title)
            ->when($this->stream->description, function ($mail) {
                return $mail->line('**Description:** ' . $this->stream->description);
            })
            ->line('Click the button below to join the stream:')
            ->action('Join Stream', $streamUrl)
            ->line('If you\'re unable to join, you can also copy and paste this URL into your browser:')
            ->line($streamUrl)
            ->line('Happy streaming!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'stream_id' => $this->stream->id,
            'stream_title' => $this->stream->title,
            'host_name' => $this->host->name,
            'host_id' => $this->host->id,
            'message' => $this->host->name . ' invited you to join the stream "' . $this->stream->title . '"',
            'action_url' => url('/streams/' . $this->stream->id),
        ];
    }
}
