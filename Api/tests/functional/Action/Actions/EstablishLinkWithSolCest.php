<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\EstablishLinkWithSol;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Communications\Entity\LinkWithSol;
use Mush\Communications\Repository\LinkWithSolRepository;
use Mush\Communications\Service\CreateLinkWithSolForDaedalusService;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
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
    private CreateLinkWithSolForDaedalusService $createLinkWithSolForDaedalus;

    private ActionConfig $actionConfig;
    private EstablishLinkWithSol $establishLinkWithSol;

    private GameEquipment $commsCenter;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->linkWithSolRepository = $I->grabService(LinkWithSolRepository::class);
        $this->createLinkWithSolForDaedalus = $I->grabService(CreateLinkWithSolForDaedalusService::class);

        $this->actionConfig = $I->grabEntityFromRepository(
            ActionConfig::class,
            params: ['name' => ActionEnum::ESTABLISH_LINK_WITH_SOL->toString()]
        );
        $this->establishLinkWithSol = $I->grabService(EstablishLinkWithSol::class);

        $this->givenLinkWithSolIsNotEstablished();
        $this->givenACommsCenterInChunRoom();
        $this->givenChunIsFocusedOnCommsCenter();
    }

    public function shouldIncreaseLinkStrength(FunctionalTester $I): void
    {
        $this->givenLinkWithSolStrengthIs(12);

        $this->whenChunEstablishLinkWithSol();

        $this->thenLinkStrengthIs($I, 16);
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
        $this->createLinkWithSolForDaedalus->execute($this->daedalus->getId());
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

    private function givenLinkWithSolStrengthIs(int $strength): void
    {
        $linkWithSol = $this->getLinkWithSol();
        $linkWithSol->increaseStrength($strength);
    }

    private function thenLinkStrengthIs(FunctionalTester $I, int $expectedStrength): void
    {
        $I->assertEquals($this->getLinkWithSol()->getStrength(), $expectedStrength, message: "Link strength should be $expectedStrength");
    }
}
