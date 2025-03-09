<?php

declare(strict_types=1);

namespace Mush\Communications\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Communications\Dto\XylophConfigDto;
use Mush\Communications\Entity\XylophConfig;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;

final class XylophConfigDataLoader extends ConfigDataLoader
{
    private const XYLOPH_CONFIG_FILE_NAME = 'src/Communications/ConfigData/xyloph_config_data.json';

    public function loadConfigsData(): void
    {
        foreach ($this->getDtosFromJsonFile(self::XYLOPH_CONFIG_FILE_NAME) as $xylophConfigDto) {
            /** @var ?XylophConfig $xylophConfig */
            $xylophConfig = $this->entityManager->getRepository(XylophConfig::class)->findOneBy(['key' => $xylophConfigDto->key]);

            $newXylophConfig = new XylophConfig(
                $xylophConfigDto->key,
                $xylophConfigDto->name,
                $xylophConfigDto->weight,
                $xylophConfigDto->quantity,
                $this->getModifierConfigs($xylophConfigDto->modifierConfigs)
            );

            if ($xylophConfig === null) {
                $xylophConfig = $newXylophConfig;
            } else {
                $xylophConfig->update($newXylophConfig);
            }

            $this->entityManager->persist($xylophConfig);
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

    /**
     * @return XylophConfigDto[]
     */
    private function getDtosFromJsonFile(string $fileName): array
    {
        $jsonFile = file_get_contents($fileName);
        $data = json_decode($jsonFile, true);

        return array_map(static fn (array $data) => XylophConfigDto::fromJson($data), $data);
    }
}
