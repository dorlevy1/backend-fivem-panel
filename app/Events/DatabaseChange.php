<?php

namespace App\Events;

use AllowDynamicProperties;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

#[AllowDynamicProperties] class DatabaseChange implements ShouldBroadcastNow

{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $data = ['sender' => 'Outlawz Microservice'];

    public string $change;
    public mixed $dataDB;
    public mixed $name;

    public function __construct($name, $event)
    {
        $this->name = $name;
        $this->event = $event;
        $this->dataDB = [];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @param $data
     *
     * @return void
     */

    public function setData($data): void
    {
        $this->dataDB = $data;
    }

    public function broadcastOn(): Channel
    {

        return new Channel($this->name);
    }

    public function send($notification): \Illuminate\Broadcasting\PendingBroadcast
    {
        return broadcast($notification)->toOthers();
    }

    public function broadcastAs()
    {
        return $this->event;
    }

    public function broadcastWith(): array
    {

        $sender = $this->data['sender'];

        return ['sender' => $sender, 'data' => $this->dataDB];
    }
}
