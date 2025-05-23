<?php

// src/Repository/MessageRepository.php
namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function save(Message $message): void
    {
        $this->getEntityManager()->persist($message);
        $this->getEntityManager()->flush();
    }

    public function findAllOrderedByTimestamp(): array
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.timestamp', 'ASC')
            ->getQuery()
            ->getResult();
    }
}