<?php

namespace Mush\Tests\functional\Game\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class DaedalusCycleChangeCest
{
    private CycleServiceInterface $cycleService;

    private \DateTime $currentTime;
    private Daedalus $daedalus;
    private Player $player;

    public function _before(FunctionalTester $I)
    {
        $this->cycleService = $I->grabService(CycleServiceInterface::class);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDiseaseName(DiseaseEnum::FOOD_POISONING)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($diseaseConfig);
        $diseaseCause = new DiseaseCauseConfig();
        $diseaseCause
            ->setCauseName(DiseaseCauseEnum::TRAUMA)
            ->setDiseases([
                DiseaseEnum::FOOD_POISONING => 2,
            ])
            ->buildName(GameConfigENum::TEST)
        ;
        $I->haveInRepository($diseaseCause);

        $fullStomachConfig = new StatusConfig();
        $fullStomachConfig->setStatusName(PlayerStatusEnum::FULL_STOMACH)->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($fullStomachConfig);

        $fireStatusConfig = new ChargeStatusConfig();
        $fireStatusConfig->setStatusName(StatusEnum::FIRE)->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($fireStatusConfig);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        /** @var DaedalusConfig $daedalusConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class, [
            'initOxygen' => 2000,
            'initHull' => 5000,
            'initHunterPoints' => 40,
            'maxOxygen' => 2000,
            'maxHull' => 5000,
            'cyclePerGameDay' => 10,
        ]);
        $hunterConfigs = $I->grabEntitiesFromRepository(HunterConfig::class);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'daedalusConfig' => $daedalusConfig,
            'localizationConfig' => $localizationConfig,
            'diseaseCauseConfig' => new ArrayCollection([$diseaseCause]),
            'diseaseConfig' => new ArrayCollection([$diseaseConfig]),
            'statusConfigs' => new ArrayCollection([$fullStomachConfig, $fireStatusConfig]),
            'hunterConfigs' => new ArrayCollection($hunterConfigs),
        ]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        $this->currentTime = new \DateTime();
        $lastCycle = $this->currentTime->sub(new \DateInterval('PT151H')); // subtract 150 h (ie 50 cycles)

        $this->daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $this->daedalus->setDaedalusVariables($daedalusConfig);
        $daedalusInfo = new DaedalusInfo($this->daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setNeron($neron)
            ->setGameStatus(GameStatusEnum::CURRENT)
        ;
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $this->daedalus]);
        $space = $I->have(Place::class, ['daedalus' => $this->daedalus, 'name' => 'space']);

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['name' => 'test']);
        $characterConfig
            ->setInitHealthPoint(20000)
            ->setInitMoralPoint(20000)
            ->setMaxHealthPoint(20000)
            ->setMaxMoralPoint(20000)
        ;
        $I->haveInRepository($characterConfig);

        /* @var Player $this->player */
        $this->player = $I->have(
            Player::class, [
                'daedalus' => $this->daedalus,
                'place' => $room,
            ]
        );
        $this->player->setPlayerVariables($characterConfig);
        $this->player->setVariableValueByName(PlayerVariableEnum::SATIETY, 5000);
        $playerInfo = new PlayerInfo($this->player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $this->player->setPlayerInfo($playerInfo);
        $I->haveInRepository($this->player);
    }

    public function testChangeManyCyclesSubscriber(FunctionalTester $I)
    {
        $this->cycleService->handleCycleChange($this->currentTime, $this->daedalus);

        $I->assertEquals($this->daedalus->getDaedalusInfo()->getGameStatus(), GameStatusEnum::CURRENT);
        $I->assertEquals($this->player->getPlayerInfo()->getGameStatus(), GameStatusEnum::CURRENT);
        $I->assertFalse($this->daedalus->isCycleChange());
        $I->assertEquals($this->daedalus->getDay(), 6);
    }

    public function testMultipleCycleChangeCallsTriggerItOnlyOnce(FunctionalTester $I): void
    {
        $lastCycle = (new \DateTime())->sub(new \DateInterval('PT3H1M')); // subtract 3 h and 1 minute (ie 1 cycle)
        $now = new \DateTime();

        $this->daedalus->setCycleStartedAt($lastCycle);
        $I->haveInRepository($this->daedalus);

        $I->assertFalse($this->daedalus->isCycleChange());
        for ($i = 0; $i < 10; ++$i) {
            $this->cycleService->handleCycleChange($now, $this->daedalus);
        }

        $I->assertFalse($this->daedalus->isCycleChange());
        $I->assertEquals($this->daedalus->getCycle(), 2);
    }
}
