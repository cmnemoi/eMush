<?php

namespace Mush\Communication\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Daedalus\Entity\Daedalus;

class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function findNeronCycleReport(Daedalus $daedalus): ?Message
    {
        $queryBuilder = $this->createQueryBuilder('message');

        $queryBuilder
            ->where($queryBuilder->expr()->eq('message.neron', ':neron'))
            ->andWhere($queryBuilder->expr()->gte('message.createdAt', ':cycleStart'))
            ->andWhere($queryBuilder->expr()->eq('message.message', ':failureMessage'))
            ->setParameter('neron', $daedalus->getDaedalusInfo()->getNeron()->getId())
            ->setParameter('cycleStart', $daedalus->getCycleStartedAt())
            ->setParameter('failureMessage', NeronMessageEnum::CYCLE_FAILURES)
        ;

        $results = $queryBuilder->getQuery()->getResult();

        if (count($results) === 0) {
            return null;
        }

        return current($results);
    }
}
