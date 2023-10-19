<?php

declare(strict_types=1);

namespace Mush\Daedalus\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Exploration\Enum\SpaceOrientationEnum;

final class DaedalusTravelService implements DaedalusTravelServiceInterface
{   
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function turnDaedalusLeft(Daedalus $daedalus): Daedalus
    {   
        $daedalus->setOrientation(SpaceOrientationEnum::getCounterClockwiseOrientation($daedalus->getOrientation()));

        $this->persist([$daedalus]);
        
        return $daedalus;
    }

    private function persist(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();
    }
}