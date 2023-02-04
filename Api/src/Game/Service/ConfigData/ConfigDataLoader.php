<?php

namespace Mush\Game\Service\ConfigData;

use Doctrine\ORM\EntityManagerInterface;

abstract class ConfigDataLoader
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Load configs data.
     */
    abstract public function loadConfigsData(): void;
}
