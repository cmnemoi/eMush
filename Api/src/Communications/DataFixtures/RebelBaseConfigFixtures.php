<?php

declare(strict_types=1);

namespace Mush\Communications\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Communications\Dto\RebelBaseConfigDto;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Modifier\DataFixtures\RebelBaseModifierConfigFixtures;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Status\DataFixtures\StatusFixtures;
use Mush\Status\Entity\Config\StatusConfig;

final class RebelBaseConfigFixtures extends Fixture implements DependentFixtureInterface
{
    private const REBEL_BASE_CONFIG_FILE_NAME = 'src/Communications/ConfigData/rebel_base_config_data.json';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $rebelBaseConfigDtos = $this->getRebelBaseConfigDtosFromFile(self::REBEL_BASE_CONFIG_FILE_NAME);

        foreach ($rebelBaseConfigDtos as $rebelBaseConfigDto) {
            $rebelBaseConfig = new RebelBaseConfig(
                $rebelBaseConfigDto->key,
                $rebelBaseConfigDto->name,
                $rebelBaseConfigDto->contactOrder,
                $this->getModifierConfigs($rebelBaseConfigDto->modifierConfigs),
                $this->getStatusConfig($rebelBaseConfigDto->statusConfig)
            );
            $manager->persist($rebelBaseConfig);
            $gameConfig->addRebelBaseConfig($rebelBaseConfig);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            RebelBaseModifierConfigFixtures::class,
            StatusFixtures::class,
        ];
    }

    private function getModifierConfigs(array $names): ArrayCollection
    {
        /** @var ArrayCollection<int, AbstractModifierConfig> $modifierConfigs */
        $modifierConfigs = new ArrayCollection();

        foreach ($names as $name) {
            $modifierConfig = $this->getReference($name);
            if (!$modifierConfig instanceof AbstractModifierConfig) {
                throw new \Exception("Modifier config {$name} is not an instance of AbstractModifierConfig");
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

        $statusConfig = $this->getReference($name . '_status');
        if (!$statusConfig instanceof StatusConfig) {
            throw new \Exception("Status config {$name} is not an instance of StatusConfig");
        }

        return $statusConfig;
    }

    /**
     * @return RebelBaseConfigDto[]
     */
    private function getRebelBaseConfigDtosFromFile(string $fileName): array
    {
        $jsonFile = file_get_contents($fileName);
        if (!$jsonFile) {
            throw new \Exception("Failed to read JSON file {$fileName}");
        }

        $data = json_decode($jsonFile, true);
        if (!\is_array($data)) {
            throw new \Exception("Failed to decode JSON file {$fileName}");
        }

        return array_map(static fn (array $data) => RebelBaseConfigDto::fromJson($data), $data);
    }
}
