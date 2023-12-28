<?php

namespace Mush\Tests\unit\Disease\Entity\Collection;

use Mush\Disease\Entity\Collection\PlayerDiseaseCollection;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use PHPUnit\Framework\TestCase;

class PlayerDiseaseCollectTest extends TestCase
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

        $this->assertEmpty($diseaseCollection->getActiveDiseases());

        $diseaseCollection->add($incubatingPlayerDisease);
        $diseaseCollection->add($otherPlayerDisease);

        $this->assertEmpty($diseaseCollection->getActiveDiseases());

        $diseaseCollection->add($activePlayerDisease);

        $this->assertCount(1, $diseaseCollection->getActiveDiseases());
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

        $this->assertEmpty($diseaseCollection->getByDiseaseType('something'));

        $this->assertCount(1, $diseaseCollection->getByDiseaseType(MedicalConditionTypeEnum::DISEASE));
        $this->assertContains($diseaseType, $diseaseCollection->getByDiseaseType(MedicalConditionTypeEnum::DISEASE));

        $this->assertCount(1, $diseaseCollection->getByDiseaseType(MedicalConditionTypeEnum::DISORDER));
        $this->assertContains($disorderType, $diseaseCollection->getByDiseaseType(MedicalConditionTypeEnum::DISORDER));

        $this->assertCount(1, $diseaseCollection->getByDiseaseType('other'));
        $this->assertContains($otherType, $diseaseCollection->getByDiseaseType('other'));
    }
}
