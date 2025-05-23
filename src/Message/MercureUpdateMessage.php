<?php

namespace App\Message;

class MercureUpdateMessage
{
    private array $topics;
    private string $data;
    private bool $private;

    public function __construct(array $topics, string $data, bool $private = false)
    {
        $this->topics = $topics;
        $this->data = $data;
        $this->private = $private;
    }

    public function getTopics(): array
    {
        return $this->topics;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }
}