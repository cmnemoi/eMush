<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\EstablishLinkWithSol;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Communications\Entity\LinkWithSol;
use Mush\Communications\Repository\LinkWithSolRepository;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class EstablishLinkWithSolCest extends AbstractFunctionalTest
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private LinkWithSolRepository $linkWithSolRepository;

    private ActionConfig $actionConfig;
    private EstablishLinkWithSol $establishLinkWithSol;

    private GameEquipment $commsCenter;
    private GameEquipment $antenna;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->linkWithSolRepository = $I->grabService(LinkWithSolRepository::class);

        $this->actionConfig = $I->grabEntityFromRepository(
            ActionConfig::class,
            params: ['name' => ActionEnum::ESTABLISH_LINK_WITH_SOL->toString()]
        );
        $this->establishLinkWithSol = $I->grabService(EstablishLinkWithSol::class);

        $this->givenLinkWithSolIsNotEstablished();
        $this->givenAnAntennaInDaedalus();
        $this->givenACommsCenterInChunRoom();
        $this->givenChunIsFocusedOnCommsCenter();
    }

    public function shouldIncreaseLinkStrength(FunctionalTester $I): void
    {
        $this->givenLinkWithSolStrengthIs(12);

        $this->whenChunEstablishLinkWithSol();

        $this->thenLinkStrengthIs($I, 16);
    }

    public function brokenAntennaShouldIncreasesAPCost(FunctionalTester $I): void
    {
        $this->givenChunHasActionPoints(3);

        $this->givenAntennaIsBroken();

        $this->whenChunEstablishLinkWithSol();

        $this->thenChunHasActionPoints($I, 0);
    }

    public function shouldEstablishLinkWithSolOnSuccess(FunctionalTester $I): void
    {
        $this->givenLinkWithSolStrengthIs(100);

        $this->whenChunEstablishLinkWithSol();

        $this->thenLinkIsEstablished($I);
    }

    public function shouldBeExecutableOnePerDay(FunctionalTester $I): void
    {
        $this->givenChunEstablishesLinkWithSol();

        $this->whenChunTriesToEstablishLinkWithSol();

        $this->thenActionShouldNotBeExecutableWithMessage($I, ActionImpossibleCauseEnum::COMS_ALREADY_ATTEMPTED);
    }

    public function shouldNotBeExecutableIfPlayerIsDirty(FunctionalTester $I): void
    {
        $this->givenChunIsDirty();

        $this->whenChunTriesToEstablishLinkWithSol();

        $this->thenActionShouldNotBeExecutableWithMessage($I, ActionImpossibleCauseEnum::DIRTY_RESTRICTION);
    }

    private function givenACommsCenterInChunRoom(): void
    {
        $this->commsCenter = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::COMMUNICATION_CENTER,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenChunIsFocusedOnCommsCenter(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
            target: $this->commsCenter,
        );
    }

    private function getLinkWithSol(): LinkWithSol
    {
        return $this->linkWithSolRepository->findByDaedalusIdOrThrow($this->daedalus->getId());
    }

    private function givenLinkWithSolIsNotEstablished(): void
    {
        $linkWithSol = new LinkWithSol($this->daedalus->getId());
        $this->linkWithSolRepository->save($linkWithSol);
    }

    private function givenLinkWithSolStrengthIs(int $strength): void
    {
        $linkWithSol = $this->getLinkWithSol();
        $linkWithSol->increaseStrength($strength);
    }

    private function givenAnAntennaInDaedalus(): void
    {
        $this->antenna = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::ANTENNA,
            equipmentHolder: $this->daedalus->getPlaceByNameOrThrow(RoomEnum::SPACE),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenChunHasActionPoints(int $quantity): void
    {
        $this->chun->setActionPoint($quantity);
    }

    private function givenAntennaIsBroken(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->antenna,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function whenChunEstablishLinkWithSol(): void
    {
        $this->establishLinkWithSol->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->commsCenter,
            player: $this->chun,
            target: $this->commsCenter,
        );
        $this->establishLinkWithSol->execute();
    }

    private function thenLinkStrengthIs(FunctionalTester $I, int $expectedStrength): void
    {
        $I->assertEquals($this->getLinkWithSol()->getStrength(), $expectedStrength, message: "Link strength should be {$expectedStrength}");
    }

    private function thenChunHasActionPoints(FunctionalTester $I, int $quantity): void
    {
        $I->assertEquals($this->chun->getActionPoint(), $quantity, message: "Chun should have {$quantity} action points");
    }

    private function thenLinkIsEstablished(FunctionalTester $I): void
    {
        $I->assertTrue($this->getLinkWithSol()->isEstablished(), message: 'Link should be established');
    }

    private function givenChunIsDirty(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::DIRTY,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenChunEstablishesLinkWithSol(): void
    {
        $this->whenChunEstablishLinkWithSol();
    }

    private function whenChunTriesToEstablishLinkWithSol(): void
    {
        $this->establishLinkWithSol->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->commsCenter,
            player: $this->chun,
            target: $this->commsCenter,
        );
    }

    private function thenActionShouldNotBeExecutableWithMessage(FunctionalTester $I, string $message): void
    {
        $I->assertEquals($this->establishLinkWithSol->cannotExecuteReason(), $message, message: "Action should not be executable with message '{$message}'");
    }
}
