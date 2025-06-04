<?php

declare(strict_types=1);

namespace Mush\Triumph\ConfigData;

use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Triumph\Entity\TriumphConfig;

final class TriumphConfigDataLoader extends ConfigDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (TriumphConfigData::getAll() as $triumphConfigDto) {
            /** @var ?TriumphConfig $triumphConfig */
            $triumphConfig = $this->entityManager->getRepository(TriumphConfig::class)->findOneBy(['name' => $triumphConfigDto->name]);

            if (!$triumphConfig) {
                $triumphConfig = TriumphConfig::fromDto($triumphConfigDto);
            } else {
                $triumphConfig->updateFromDto($triumphConfigDto);
            }

            $this->entityManager->persist($triumphConfig);
        }
        $this->entityManager->flush();
    }
}
