<?php

declare(strict_types=1);

namespace Mush\Communications\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Communications\Dto\RebelBaseConfigDto;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Status\Entity\Config\StatusConfig;

final class RebelBaseConfigDataLoader extends ConfigDataLoader
{
    private const REBEL_BASE_CONFIG_FILE_NAME = 'src/Communications/ConfigData/rebel_base_config_data.json';

    public function loadConfigsData(): void
    {
        foreach ($this->getDtosFromJsonFile(self::REBEL_BASE_CONFIG_FILE_NAME) as $rebelBaseConfigDto) {
            /** @var ?RebelBaseConfig $rebelBaseConfig */
            $rebelBaseConfig = $this->entityManager->getRepository(RebelBaseConfig::class)->findOneBy(['key' => $rebelBaseConfigDto->key]);

            $newRebelBaseConfig = new RebelBaseConfig(
                $rebelBaseConfigDto->key,
                $rebelBaseConfigDto->name,
                $rebelBaseConfigDto->contactOrder,
                $this->getModifierConfigs($rebelBaseConfigDto->modifierConfigs),
                $this->getStatusConfig($rebelBaseConfigDto->statusConfig)
            );

            if ($rebelBaseConfig === null) {
                $rebelBaseConfig = $newRebelBaseConfig;
            } else {
                $rebelBaseConfig->update($newRebelBaseConfig);
            }

            $this->entityManager->persist($rebelBaseConfig);
        }

        $this->entityManager->flush();
    }

    private function getModifierConfigs(array $names): ArrayCollection
    {
        /** @var ArrayCollection<int, AbstractModifierConfig> $modifierConfigs */
        $modifierConfigs = new ArrayCollection();
        $modifierConfigRepository = $this->entityManager->getRepository(AbstractModifierConfig::class);
        foreach ($names as $name) {
            /** @var ?AbstractModifierConfig $modifierConfig */
            $modifierConfig = $modifierConfigRepository->findOneBy(['name' => $name]);
            if ($modifierConfig === null) {
                throw new \Exception("Modifier config {$name} not found");
            }
            $modifierConfigs->add($modifierConfig);
        }

        return $modifierConfigs;
    }

    private function getStatusConfig(?string $name): ?StatusConfig
    {
        if ($name === null) {
            return null;
        }

        $statusConfigRepository = $this->entityManager->getRepository(StatusConfig::class);

        return $statusConfigRepository->findOneBy(['name' => $name . '_default']);
    }

    /**
     * @return RebelBaseConfigDto[]
     */
    private function getDtosFromJsonFile(string $fileName): array
    {
        $data = file_get_contents($fileName);
        if (!$data) {
            throw new \Exception("Failed to read JSON file {$fileName}");
        }

        $data = json_decode($data, true);
        if (!\is_array($data)) {
            throw new \Exception("Failed to decode JSON file {$fileName}");
        }

        return array_map(static fn (array $data) => RebelBaseConfigDto::fromJson($data), $data);
    }
}
