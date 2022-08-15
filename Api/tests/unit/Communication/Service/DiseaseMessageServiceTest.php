<?php

namespace Mush\Test\Communication\Service;

use Mockery;
use Mush\Communication\Services\DiseaseMessageService;
use Mush\Communication\Services\DiseaseMessageServiceInterface;
use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;

class DiseaseMessageServiceTest extends TestCase
{
    private DiseaseMessageServiceInterface $service;

    /**
     * @before
     */
    public function before()
    {
        $this->service = new DiseaseMessageService();
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testDeafPlayer()
    {
        $player = new Player();

        $symptomConfig = new SymptomConfig(SymptomEnum::DEAF);
        $symptomConfig->setTrigger(EventEnum::ON_NEW_MESSAGE);
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]));
        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
        ;

        $player->addMedicalCondition($playerDisease);

        $message = $this->service->applyDiseaseEffects('some message', $player);

        $this->assertEquals('SOME MESSAGE', $message);
    }
}
