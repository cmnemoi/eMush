<?php

declare(strict_types=1);

namespace Mush\Communications\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Communications\Dto\XylophConfigDto;
use Mush\Communications\Entity\XylophConfig;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Modifier\DataFixtures\XylophModifierConfigFixtures;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;

final class XylophConfigFixtures extends Fixture implements DependentFixtureInterface
{
    private const XYLOPH_CONFIG_FILE_NAME = 'src/Communications/ConfigData/xyloph_config_data.json';

    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        $xylophConfigDtos = $this->getXylophConfigDtosFromFile(self::XYLOPH_CONFIG_FILE_NAME);

        foreach ($xylophConfigDtos as $xylophConfigDto) {
            $xylophConfig = new XylophConfig(
                $xylophConfigDto->key,
                $xylophConfigDto->name,
                $xylophConfigDto->weight,
                $this->getModifierConfigs($xylophConfigDto->modifierConfigs)
            );
            $manager->persist($xylophConfig);
            $gameConfig->addXylophConfig($xylophConfig);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            XylophModifierConfigFixtures::class,
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

    /**
     * @return XylophConfigDto[]
     */
    private function getXylophConfigDtosFromFile(string $fileName): array
    {
        $jsonFile = file_get_contents($fileName);
        $data = json_decode($jsonFile, true);

        return array_map(static fn (array $data) => XylophConfigDto::fromJson($data), $data);
    }
}
