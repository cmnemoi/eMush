<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Communications\Event;

use Mush\Communications\Entity\LinkWithSol;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;
use Mush\Communications\Service\CreateLinkWithSolForDaedalusService;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class CommsCenterBrokenEventCest extends AbstractFunctionalTest
{
    private CreateLinkWithSolForDaedalusService $createLinkWithSolForDaedalus;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private LinkWithSolRepositoryInterface $linkWithSolRepository;

    private GameEquipment $commsCenter;
    private LinkWithSol $linkWithSol;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->createLinkWithSolForDaedalus = $I->grabService(CreateLinkWithSolForDaedalusService::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->linkWithSolRepository = $I->grabService(LinkWithSolRepositoryInterface::class);

        $this->createLinkWithSol();
        $this->givenACommsCenter();
    }

    public function shouldKillLinkWithSol(FunctionalTester $I): void
    {
        $this->givenLinkWithSolIsEstablished();

        $this->whenCommsCenterBreaks();

        $this->thenLinkWithSolIsNotEstablished($I);
    }

    private function givenLinkWithSolIsEstablished(): void
    {
        $this->linkWithSol->establish();
        $this->linkWithSolRepository->save($this->linkWithSol);
    }

    private function whenCommsCenterBreaks(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->commsCenter,
            tags: [],
            time: new \DateTime()
        );
    }

    private function thenLinkWithSolIsNotEstablished(FunctionalTester $I): void
    {
        $I->assertFalse($this->linkWithSol->isEstablished(), 'Link with Sol should not be established');
    }

    private function givenACommsCenter(): void
    {
        $this->commsCenter = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::COMMUNICATION_CENTER,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function createLinkWithSol(): void
    {
        $this->createLinkWithSolForDaedalus->execute($this->daedalus->getId());
        $this->linkWithSol = $this->linkWithSolRepository->findByDaedalusIdOrThrow($this->daedalus->getId());
    }
}
