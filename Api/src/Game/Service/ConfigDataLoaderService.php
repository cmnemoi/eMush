<?php

namespace Mush\Game\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Game\ConfigData\ConfigDataLoader;

class ConfigDataLoaderService
{
    private ArrayCollection $dataLoaders;

    public function __construct()
    {
        $this->setDataLoaders(new ArrayCollection());

        // TODO: Import all ConfigDataLoaders here
    }

    public function loadAllConfigsData(): void
    {
        /** @var ConfigDataLoader $dataLoader */
        foreach ($this->dataLoaders as $dataLoader) {
            $dataLoader->loadConfigsData();
        }
    }

    /** @psalm-param ArrayCollection<int, ConfigDataLoader> $dataLoaders **/
    private function setDataLoaders(ArrayCollection $dataLoaders): void
    {
        $this->dataLoaders = $dataLoaders;
    }
}
