<?php

namespace Mush\Tests\Functional\Action\Actions;

use Mush\Action\Actions\CheckRoster;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class CheckRosterCest extends AbstractFunctionalTest
{
    private ActionConfig $checkRosterActionConfig;
    private CheckRoster $checkRoster;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private Place $laboratory;
    private GameEquipment $cryoModule;
    private Player $janice;
    private Player $raluca;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->checkRosterActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::CHECK_ROSTER]);
        $this->checkRoster = $I->grabService(CheckRoster::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->laboratory = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
        $this->cryoModule = $this->givenACryoModuleInLaboratory();
    }

    public function shouldDisplayRoster(FunctionalTester $I): void
    {
        $this->givenJaniceIsInDaedalus($I);
        $this->givenRalucaIsInDaedalus($I);
        $this->givenJaniceIsDead();
        $this->givenRalucaIsInactive();

        $this->whenPlayerCheckRoster();

        $this->thenRosterShouldBeDisplayed($I);
    }

    private function givenACryoModuleInLaboratory(): GameEquipment
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::CRYO_MODULE,
            equipmentHolder: $this->laboratory,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function whenPlayerCheckRoster(): void
    {
        $this->checkRoster->loadParameters(
            actionConfig: $this->checkRosterActionConfig,
            actionProvider: $this->cryoModule,
            player: $this->player,
            target: $this->cryoModule,
        );
        $this->checkRoster->execute();
    }

    private function givenJaniceIsInDaedalus(FunctionalTester $I): void
    {
        $this->janice = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JANICE);
    }

    private function givenRalucaIsInDaedalus(FunctionalTester $I): void
    {
        $this->raluca = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::RALUCA);
    }

    private function givenJaniceIsDead(): void
    {
        $this->janice->kill();
    }

    private function givenRalucaIsInactive(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::INACTIVE,
            holder: $this->raluca,
            tags: [],
            time: new \DateTime()
        );
    }

    private function thenRosterShouldBeDisplayed(FunctionalTester $I): void
    {
        $roomLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->laboratory->getName(),
                'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                'playerInfo' => $this->player->getPlayerInfo(),
                'visibility' => VisibilityEnum::PRIVATE,
            ]
        );

        $I->assertEquals(
            expected: [
                ['entry' => ['Chun', 'Éveillée']],
                ['entry' => ['Kuan Ti', 'Éveillé']],
                ['entry' => ['Janice', 'Morte']],
                ['entry' => ['Raluca', 'Inactive']],
                ['entry' => ['???', 'Cryogénisé']],
                ['entry' => ['???', 'Cryogénisé']],
                ['entry' => ['???', 'Cryogénisé']],
                ['entry' => ['???', 'Cryogénisé']],
                ['entry' => ['???', 'Cryogénisé']],
                ['entry' => ['???', 'Cryogénisé']],
                ['entry' => ['???', 'Cryogénisé']],
                ['entry' => ['???', 'Cryogénisé']],
                ['entry' => ['???', 'Cryogénisé']],
                ['entry' => ['???', 'Cryogénisé']],
                ['entry' => ['???', 'Cryogénisé']],
                ['entry' => ['???', 'Cryogénisé']],
            ],
            actual: $roomLog->getTableLog()
        );
    }
}
