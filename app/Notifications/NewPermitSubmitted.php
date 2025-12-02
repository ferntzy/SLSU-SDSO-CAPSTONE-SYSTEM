<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NewPermitSubmitted extends Notification
{
    use Queueable;

    protected $permit;
    protected $organization;

    public function __construct($permit, $organization)
    {
        $this->permit = $permit;
        $this->organization = $organization;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast']; // remove 'broadcast' if you don't use Pusher
    }

    public function toArray($notifiable)
    {
        return [
            'permit_id' => $this->permit->permit_id,
            'title' => $this->permit->title_activity,
            'organization_name' => $this->organization->organization_name,
            'message' => 'submitted a new permit for your approval',
            'url' => route('adviser.permits.review', $this->permit->permit_id), // change route name if needed
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
