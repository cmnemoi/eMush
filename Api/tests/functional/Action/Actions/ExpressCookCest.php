<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\ExpressCook;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ExpressCookCest extends AbstractFunctionalTest
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private LinkWithSolRepositoryInterface $linkWithSolRepository;

    private ActionConfig $actionConfig;
    private ExpressCook $expressCook;

    private GameEquipment $standardRation;
    private GameEquipment $microwave;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->linkWithSolRepository = $I->grabService(LinkWithSolRepositoryInterface::class);

        $this->actionConfig = $I->grabEntityFromRepository(
            ActionConfig::class,
            params: ['name' => ActionEnum::EXPRESS_COOK->toString()]
        );
        $this->expressCook = $I->grabService(ExpressCook::class);

        $this->givenAStandardRationInRoom();
        $this->givenMicrowaveIsInRoom();
    }

    public function shouldKillLinkWithSol(FunctionalTester $I): void
    {
        $this->givenLinkWithSolIsEstablished();
        $this->givenExpressCookHas100PercentsChanceToKillLinkWithSol();

        $this->whenPlayerExpressCooks();

        $this->thenLinkWithSolShouldBeKilled($I);
    }

    private function givenAStandardRationInRoom(): void
    {
        $this->standardRation = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameRationEnum::STANDARD_RATION,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
            visibility: VisibilityEnum::PUBLIC,
        );
    }

    private function givenMicrowaveIsInRoom(): void
    {
        $this->microwave = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ToolItemEnum::MICROWAVE,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenLinkWithSolIsEstablished(): void
    {
        $linkWithSol = $this->linkWithSolRepository->findByDaedalusIdOrThrow($this->daedalus->getId());
        $linkWithSol->establish();
    }

    private function givenExpressCookHas100PercentsChanceToKillLinkWithSol(): void
    {
        $this->actionConfig->setOutputQuantity(100);
    }

    private function whenPlayerExpressCooks(): void
    {
        $this->expressCook->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->microwave,
            player: $this->player,
            target: $this->standardRation,
        );
        $this->expressCook->execute();
    }

    private function thenLinkWithSolShouldBeKilled(FunctionalTester $I): void
    {
        $linkWithSol = $this->linkWithSolRepository->findByDaedalusIdOrThrow($this->daedalus->getId());
        $I->assertTrue($linkWithSol->isNotEstablished(), 'Link with Sol should not be established');
    }
}
