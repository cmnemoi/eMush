<?php

namespace Mush\Tests\unit\Disease\Entity\Collection;

use Mush\Disease\Entity\Collection\PlayerDiseaseCollection;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PlayerDiseaseCollectTest extends TestCase
{
    public function testGetActiveDisease()
    {
        $activePlayerDisease = new PlayerDisease();
        $activePlayerDisease->setStatus(DiseaseStatusEnum::ACTIVE);
        $incubatingPlayerDisease = new PlayerDisease();
        $incubatingPlayerDisease->setStatus(DiseaseStatusEnum::INCUBATING);
        $otherPlayerDisease = new PlayerDisease();
        $otherPlayerDisease->setStatus('other status');

        $diseaseCollection = new PlayerDiseaseCollection();

        self::assertEmpty($diseaseCollection->getActiveDiseases());

        $diseaseCollection->add($incubatingPlayerDisease);
        $diseaseCollection->add($otherPlayerDisease);

        self::assertEmpty($diseaseCollection->getActiveDiseases());

        $diseaseCollection->add($activePlayerDisease);

        self::assertCount(1, $diseaseCollection->getActiveDiseases());
    }

    public function testByDiseaseType()
    {
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setType(MedicalConditionTypeEnum::DISEASE);
        $disorderConfig = new DiseaseConfig();
        $disorderConfig->setType(MedicalConditionTypeEnum::DISORDER);
        $otherConfig = new DiseaseConfig();
        $otherConfig->setType('other');

        $diseaseType = new PlayerDisease();
        $diseaseType->setDiseaseConfig($diseaseConfig);
        $disorderType = new PlayerDisease();
        $disorderType->setDiseaseConfig($disorderConfig);
        $otherType = new PlayerDisease();
        $otherType->setDiseaseConfig($otherConfig);

        $diseaseCollection = new PlayerDiseaseCollection([
            $diseaseType, $disorderType, $otherType,
        ]);

        self::assertEmpty($diseaseCollection->getByDiseaseType('something'));

        self::assertCount(1, $diseaseCollection->getByDiseaseType(MedicalConditionTypeEnum::DISEASE));
        self::assertContains($diseaseType, $diseaseCollection->getByDiseaseType(MedicalConditionTypeEnum::DISEASE));

        self::assertCount(1, $diseaseCollection->getByDiseaseType(MedicalConditionTypeEnum::DISORDER));
        self::assertContains($disorderType, $diseaseCollection->getByDiseaseType(MedicalConditionTypeEnum::DISORDER));

        self::assertCount(1, $diseaseCollection->getByDiseaseType('other'));
        self::assertContains($otherType, $diseaseCollection->getByDiseaseType('other'));
    }
}
