<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\BypassTerminal;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class BypassTerminalActionCest extends AbstractFunctionalTest
{
    private BypassTerminal $bypassTerminalAction;
    private ActionConfig $bypassTerminalConfig;
    private GameEquipment $biosTerminal;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $this->bypassTerminalConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::BYPASS_TERMINAL]);
        $this->bypassTerminalAction = $I->grabService(BypassTerminal::class);

        $nexus = $this->createExtraPlace(RoomEnum::NEXUS, $I, $this->daedalus);

        // Bios terminal
        $this->biosTerminal = $this->gameEquipmentService->createGameEquipmentFromName(
            EquipmentEnum::BIOS_TERMINAL,
            $nexus,
            [],
            new \DateTime()
        );

        $this->player->setPlace($nexus);

        $this->addSkillToPlayer(SkillEnum::BYPASS, $I, $this->player);
    }

    public function shouldBeAbleToAccessBiosWithBypass(FunctionalTester $I)
    {
        $this->bypassTerminalAction->loadParameters(
            actionConfig: $this->bypassTerminalConfig,
            actionProvider: $this->player,
            player: $this->player,
            target: $this->biosTerminal
        );

        $this->bypassTerminalAction->execute();

        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::FOCUSED));
    }

    public function shouldPrintSecretLogWhenAccessingTheBios(FunctionalTester $I): void
    {
        $this->bypassTerminalAction->loadParameters(
            actionConfig: $this->bypassTerminalConfig,
            actionProvider: $this->player,
            player: $this->player,
            target: $this->biosTerminal
        );

        $this->bypassTerminalAction->execute();

        $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'log' => ActionLogEnum::BYPASS_TERMINAL_SUCCESS,
                'visibility' => VisibilityEnum::SECRET,
            ]
        );
    }
}
