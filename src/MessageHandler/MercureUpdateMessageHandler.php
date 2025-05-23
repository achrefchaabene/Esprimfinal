<?php

namespace App\MessageHandler;

use App\Message\MercureUpdateMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Psr\Log\LoggerInterface;

#[AsMessageHandler]
class MercureUpdateMessageHandler
{
    private HubInterface $hub;
    private LoggerInterface $logger;

    public function __construct(HubInterface $hub, LoggerInterface $logger)
    {
        $this->hub = $hub;
        $this->logger = $logger;
    }

    public function __invoke(MercureUpdateMessage $message)
    {
        try {
            $update = new Update(
                $message->getTopics(),
                $message->getData(),
                $message->isPrivate()
            );

            $this->hub->publish($update);
            $this->logger->info('Mercure update published successfully');
        } catch (\Exception $e) {
            $this->logger->error('Failed to publish Mercure update: ' . $e->getMessage());
        }
    }
}