<?php

namespace Mush\Tests\functional\Modifier\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Enum\ActionEnum;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class BreakRepairEquipmentSubscriberCest
{
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testRepairGearPlaceReach(FunctionalTester $I)
    {
        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::BROKEN)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($statusConfig);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['statusConfigs' => new ArrayCollection([$statusConfig])]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'neron' => $neron]);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => GameConfigEnum::TEST]);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setNeron($neron);
        $I->haveInRepository($daedalusInfo);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig]);
        $user = new User();
        $user
            ->setUserId('blabla')
            ->setUsername('blabla')
        ;
        $I->haveInRepository($user);

        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);

        $modifierConfig = new VariableEventModifierConfig('modifierShowerActionTest');
        $modifierConfig
            ->setTargetEvent(ActionEnum::SHOWER)
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->buildName()
        ;
        $I->haveInRepository($modifierConfig);

        $modifier = new GameModifier($daedalus, $modifierConfig);
        $I->haveInRepository($modifier);

        $gear = new Gear();
        $gear
            ->setModifierConfigs(new ArrayCollection([$modifierConfig]))
            ->setName('gear_test')
        ;
        $I->haveInRepository($gear);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['mechanics' => new ArrayCollection([$gear])]);

        // Case of a game Equipment
        $gameEquipment = new GameItem($player);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
        ;
        $I->haveInRepository($gameEquipment);

        $this->statusService->createStatusFromName(
            EquipmentStatusEnum::BROKEN,
            $gameEquipment,
            [ActionEnum::COFFEE],
            new \DateTime(),
            null,
            VisibilityEnum::PUBLIC
        );

        $I->assertEquals($room->getEquipments()->count(), 0);
        $I->assertEquals($player->getEquipments()->count(), 1);
        $I->assertEquals($player->getModifiers()->count(), 0);
        $I->assertEquals($room->getModifiers()->count(), 0);
        $I->assertEquals($daedalus->getModifiers()->count(), 0);

        // now fix the equipment
        $this->statusService->removeStatus(
            EquipmentStatusEnum::BROKEN,
            $gameEquipment,
            [ActionEnum::COFFEE],
            new \DateTime(),
            VisibilityEnum::PUBLIC
        );

        $I->assertEquals($room->getEquipments()->count(), 0);
        $I->assertEquals($player->getEquipments()->count(), 1);
        $I->assertEquals($player->getModifiers()->count(), 0);
        $I->assertEquals($room->getModifiers()->count(), 0);
        $I->assertEquals($daedalus->getModifiers()->count(), 1);
        $I->assertEquals($daedalus->getModifiers()->first()->getModifierConfig(), $modifierConfig);
    }
}
