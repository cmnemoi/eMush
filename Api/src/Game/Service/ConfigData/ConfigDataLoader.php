<?php

namespace Mush\Game\Service;

use Doctrine\ORM\EntityManagerInterface;

abstract class ConfigDataLoader
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Load config data.
     */
    abstract public function loadConfigData(): void;
}
