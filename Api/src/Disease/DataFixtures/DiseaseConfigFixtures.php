<?php

namespace Mush\Disease\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Disease\ConfigData\DiseaseConfigData;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Modifier\DataFixtures\ModifierConfigFixtures;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;

class DiseaseConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        foreach (DiseaseConfigData::$dataArray as $diseaseConfigData) {
            $diseaseConfig = new DiseaseConfig();

            $diseaseConfig
                ->setName($diseaseConfigData['name'])
                ->setDiseaseName($diseaseConfigData['diseaseName'])
                ->setType($diseaseConfigData['type'])
                ->setResistance($diseaseConfigData['resistance'])
                ->setDelayMin($diseaseConfigData['delayMin'])
                ->setDelayLength($diseaseConfigData['delayLength'])
                ->setDiseasePointMin($diseaseConfigData['diseasePointMin'])
                ->setDiseasePointLength($diseaseConfigData['diseasePointLength'])
                ->setOverride($diseaseConfigData['override']);
            $this->setDiseaseConfigModifierConfigs($diseaseConfig, $diseaseConfigData, $manager);

            $manager->persist($diseaseConfig);
            $this->addReference($diseaseConfig->getName(), $diseaseConfig);

            $gameConfig->addDiseaseConfig($diseaseConfig);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            GameConfigFixtures::class,
            ModifierConfigFixtures::class,
        ];
    }

    private function setDiseaseConfigModifierConfigs(DiseaseConfig $diseaseConfig, array $diseaseConfigData, ObjectManager $manager): void
    {
        $modifierConfigs = [];
        foreach ($diseaseConfigData['modifierConfigs'] as $modifierConfigName) {
            /** @var AbstractModifierConfig $modifierConfig */
            $modifierConfig = $manager->getRepository(AbstractModifierConfig::class)->findOneBy(['name' => $modifierConfigName]);
            if ($modifierConfig === null) {
                throw new \Exception('Modifier config not found: ' . $modifierConfigName);
            }
            $modifierConfigs[] = $modifierConfig;
        }
        $diseaseConfig->setModifierConfigs($modifierConfigs);
    }
}
