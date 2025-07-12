<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Phagocyte;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PhagocyteActionCest extends AbstractFunctionalTest
{
    private Phagocyte $phagocyteAction;
    private ActionConfig $actionConfig;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->phagocyteAction = $I->grabService(Phagocyte::class);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::PHAGOCYTE]);
    }

    public function testPhagocyteWithOneSpore(FunctionalTester $I)
    {
        // given Chun is mush
        $this->convertPlayerToMush($I, $this->player);

        // given Chun has the skill phagocyte
        $this->addSkillToPlayer(SkillEnum::PHAGOCYTE, $I, $this->player);

        // given Chan has those values
        $this->player
            ->setActionPoint(1)
            ->setHealthPoint(1)
            ->setSpores(1);

        // when Chun phagocyte
        $this->phagocyteAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->player,
            player: $this->player
        );
        $this->phagocyteAction->execute();

        // then Chun should have those values
        $I->assertEquals(0, $this->player->getSpores());
        $I->assertEquals(5, $this->player->getActionPoint());
        $I->assertEquals(5, $this->player->getHealthPoint());

        // then Chun should see a log
        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player->getPlayerInfo(),
            'visibility' => VisibilityEnum::PRIVATE,
            'log' => ActionLogEnum::PHAGOCYTE_SUCCESS,
        ]);
    }
}
