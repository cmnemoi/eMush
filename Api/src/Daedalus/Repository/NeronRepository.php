<?php

declare(strict_types=1);

namespace Mush\Daedalus\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\Neron;

final class NeronRepository extends ServiceEntityRepository implements NeronRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Neron::class);
    }

    public function save(Neron $neron): void
    {
        $this->_em->persist($neron);
        $this->_em->flush();
    }
}
