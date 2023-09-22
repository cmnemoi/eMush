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

    public function _before(FunctionalTester $I)
    {
        $this->cycleService = $I->grabService(CycleServiceInterface::class);
    }

    public function testChangeManyCyclesSubscriber(FunctionalTester $I)
    {
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

        $time = new \DateTime();
        $lastCycle = $time->sub(new \DateInterval('PT151H')); // subtract 150 h (ie 50 cycles)

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $daedalus->setDaedalusVariables($daedalusConfig);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
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
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        $space = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'space']);

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

        /** @var Player $player */
        $player = $I->have(
            Player::class, [
                'daedalus' => $daedalus,
                'place' => $room,
            ]
        );
        $player->setPlayerVariables($characterConfig);
        $player->setVariableValueByName(PlayerVariableEnum::SATIETY, 5000);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $this->cycleService->handleCycleChange($time, $daedalus);

        $I->assertEquals($daedalusInfo->getGameStatus(), GameStatusEnum::CURRENT);
        $I->assertEquals($playerInfo->getGameStatus(), GameStatusEnum::CURRENT);
        $I->assertFalse($daedalus->isCycleChange());
        $I->assertEquals($daedalus->getDay(), 6);
    }
}
