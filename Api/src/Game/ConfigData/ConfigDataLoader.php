<?php

namespace Mush\Game\ConfigData;

use Doctrine\ORM\EntityManagerInterface;

abstract class ConfigDataLoader
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Load configs data.
     */
    abstract public function loadConfigsData(): void;
}
