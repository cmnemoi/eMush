<?php

namespace Mush\Player\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Player\Entity\DeadPlayerInfo;
use Mush\Player\Entity\Player;

class DeadPlayerInfoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeadPlayerInfo::class);
    }
}
