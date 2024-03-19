<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Exploration\Entity\Exploration;
use Mush\Player\Entity\Player;

final class AddPlayerToExplorationTeamService implements AddPlayerToExplorationTeamServiceInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function execute(Player $player, Exploration $exploration): void
    {
        $exploration->addExplorator($player);
        $this->entityManager->persist($exploration);
        $this->entityManager->flush();
    }
}
