<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\AccessTerminal;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class AccessTerminalActionCest extends AbstractFunctionalTest
{
    private StatusServiceInterface $statusService;
    private AccessTerminal $accessTerminal;
    private Action $accessTerminalConfig;
    private GameEquipment $astroTerminal;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $bridge = $this->createExtraPlace(RoomEnum::BRIDGE, $I, $this->daedalus);

        $this->accessTerminalConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::ACCESS_TERMINAL]);
        $this->accessTerminal = $I->grabService(AccessTerminal::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // Astro terminal
        $astroTerminalConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::ASTRO_TERMINAL]);
        $this->astroTerminal = new GameEquipment($bridge);
        $this->astroTerminal
            ->setName(EquipmentEnum::ASTRO_TERMINAL)
            ->setEquipment($astroTerminalConfig)
        ;
        $I->haveInRepository($this->astroTerminal);

        // given player is on the bridge
        $this->player->changePlace($bridge);
    }

    public function testAccessTerminalSuccessAddFocusedStatus(FunctionalTester $I): void
    {
        // given player is not focus on command terminal
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::FOCUSED));

        // given player is focused on command terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
            target: $this->astroTerminal,
        );

        // when player access terminal
        $this->accessTerminal->loadParameters($this->accessTerminalConfig, $this->player, $this->astroTerminal);
        $this->accessTerminal->execute();

        // then player is not focused on command terminal anymore
        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::FOCUSED));
    }
}
