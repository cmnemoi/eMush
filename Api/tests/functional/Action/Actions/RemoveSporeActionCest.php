<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\RemoveSpore;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class RemoveSporeActionCest extends AbstractFunctionalTest
{
    private RemoveSpore $removeSpore;

    private ActionConfig $actionConfig;

    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->removeSpore = $I->grabService(RemoveSpore::class);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::REMOVE_SPORE]);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function testRemoveSpore(FunctionalTester $I)
    {
        // given the item is in the room
        $extractor = $this->createEquipment(ToolItemEnum::SPORE_SUCKER, $this->player->getPlace());

        // given the player has those values
        $this->player
            ->setHealthPoint(9)
            ->setSpores(1);

        // when the player tries to remove a spore
        $this->removeSpore->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $extractor,
            player: $this->player,
            target: $extractor
        );
        $this->removeSpore->execute();

        // then they should have no spore and less HP
        $I->assertEquals(6, $this->player->getHealthPoint());
        $I->assertEquals(0, $this->player->getSpores());

        // then they should see the success log
        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player->getPlayerInfo(),
            'visibility' => VisibilityEnum::PRIVATE,
            'log' => ActionLogEnum::REMOVE_SPORE_SUCCESS,
        ]);

        // when the player tries to remove a spore again
        $this->removeSpore->execute();

        // then they should have no spore and less HP
        $I->assertEquals(3, $this->player->getHealthPoint());
        $I->assertEquals(0, $this->player->getSpores());

        // then they should see the failure log
        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player->getPlayerInfo(),
            'visibility' => VisibilityEnum::PRIVATE,
            'log' => ActionLogEnum::REMOVE_SPORE_FAIL,
        ]);
    }

    public function testRemoveSporeAsMush(FunctionalTester $I)
    {
        // given the item is in the room
        $extractor = $this->createEquipment(ToolItemEnum::SPORE_SUCKER, $this->player->getPlace());

        // given player is Mush
        $this->convertPlayerToMush($I, $this->player);

        // given the player has those values
        $this->player
            ->setHealthPoint(9)
            ->setSpores(1);

        // when the player tries to remove a spore
        $this->removeSpore->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $extractor,
            player: $this->player,
            target: $extractor
        );
        $this->removeSpore->execute();

        // then they should have 1 spore and less HP
        $I->assertEquals(6, $this->player->getHealthPoint());
        $I->assertEquals(1, $this->player->getSpores());

        // then they should see the mush log
        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player->getPlayerInfo(),
            'visibility' => VisibilityEnum::PRIVATE,
            'log' => ActionLogEnum::REMOVE_SPORE_MUSH,
        ]);
    }
}
