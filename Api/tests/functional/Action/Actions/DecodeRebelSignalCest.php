<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\DecodeRebelSignal;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Communications\Service\CreateLinkWithSolForDaedalusService;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Exception\GameException;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DecodeRebelSignalCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private DecodeRebelSignal $decodeRebelBase;

    private CreateLinkWithSolForDaedalusService $createLinkWithSol;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private LinkWithSolRepositoryInterface $linkWithSolRepository;
    private RebelBaseRepositoryInterface $rebelBaseRepository;
    private StatusServiceInterface $statusService;

    private GameEquipment $commsCenter;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::DECODE_REBEL_SIGNAL]);
        $this->decodeRebelBase = $I->grabService(DecodeRebelSignal::class);

        $this->createLinkWithSol = $I->grabService(CreateLinkWithSolForDaedalusService::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->linkWithSolRepository = $I->grabService(LinkWithSolRepositoryInterface::class);
        $this->rebelBaseRepository = $I->grabService(RebelBaseRepositoryInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->createLinkWithSol->execute($this->daedalus->getId());
        $this->givenCommsCenterInRoom();
    }

    public function shouldNotBeVisibleIfPlayerIsNotFocusedOnCommsCenter(FunctionalTester $I): void
    {
        $this->whenPlayerTriesToDecodeRebelSignal();

        $this->thenActionIsNotVisible($I);
    }

    public function shouldNotBeExecutableIfPlayerIsNotCommsManager(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();

        $this->whenPlayerTriesToDecodeRebelSignal();

        $this->thenActionIsNotExecutableWithMessage(ActionImpossibleCauseEnum::COMS_NOT_OFFICER, $I);
    }

    public function shouldNotBeExecutableIfLinkWithSolIsNotEstablished(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();

        $this->whenPlayerTriesToDecodeRebelSignal();

        $this->thenActionIsNotExecutableWithMessage(ActionImpossibleCauseEnum::LINK_WITH_SOL_NOT_ESTABLISHED, $I);
    }

    public function shouldNotBeExecutableIfPlayerIsDirty(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();
        $this->givenPlayerIsDirty();

        $this->whenPlayerTriesToDecodeRebelSignal();

        $this->thenActionIsNotExecutableWithMessage(ActionImpossibleCauseEnum::DIRTY_RESTRICTION, $I);
    }

    public function shouldNotBeExecutableIfNoRebelBaseIsNotContacting(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();

        $this->whenPlayerTriesToDecodeRebelSignal();

        $this->thenActionIsNotExecutableWithMessage(ActionImpossibleCauseEnum::NO_ACTIVE_REBEL, $I);
    }

    public function shouldThrowWhenTryingToDecodeNotContactingRebelBase(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();
        $this->givenRebelBaseIsContacting(RebelBaseEnum::KALADAAN, $I);
        $this->givenNonContactingRebelBase(RebelBaseEnum::WOLF, $I);

        $I->expectThrowable(GameException::class, function () {
            $this->whenPlayerDecodesRebelSignal(RebelBaseEnum::WOLF);
        });
    }

    public function kaladaanRebelBaseShouldNotGiveSixMoralePointsOnFailure(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();
        $this->givenRebelBaseIsContacting(RebelBaseEnum::KALADAAN, $I);
        $this->givenRebelBaseSignalIsAt(RebelBaseEnum::KALADAAN, 0);
        $this->givenChunHasMoralePoints(0);
        $this->givenKuanTiHasMoralePoints(0);

        $this->whenPlayerDecodesRebelSignal(RebelBaseEnum::KALADAAN);

        $this->thenChunShouldHaveMoralePoints(0, $I);
        $this->thenKuanTiShouldHaveMoralePoints(0, $I);
    }

    public function kaladaanRebelBaseShouldGiveSixMoralePointsOnSuccess(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();
        $this->givenRebelBaseIsContacting(RebelBaseEnum::KALADAAN, $I);
        $this->givenRebelBaseSignalIsAt(RebelBaseEnum::KALADAAN, 99);
        $this->givenChunHasMoralePoints(0);
        $this->givenKuanTiHasMoralePoints(0);

        $this->whenPlayerDecodesRebelSignal(RebelBaseEnum::KALADAAN);

        $this->thenChunShouldHaveMoralePoints(6, $I);
        $this->thenKuanTiShouldHaveMoralePoints(6, $I);
    }

    private function givenCommsCenterInRoom(): void
    {
        $this->commsCenter = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::COMMUNICATION_CENTER,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function whenPlayerTriesToDecodeRebelSignal(): void
    {
        $this->decodeRebelBase->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->commsCenter,
            player: $this->player,
            target: $this->commsCenter,
        );
    }

    private function thenActionIsNotVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->decodeRebelBase->isVisible(), 'Action should not be visible');
    }

    private function givenPlayerIsFocusedOnCommsCenter(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
            target: $this->commsCenter,
        );
    }

    private function thenActionIsNotExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->decodeRebelBase->cannotExecuteReason(), "Action should not be executable with message: {$message}");
    }

    private function givenPlayerIsCommsManager(): void
    {
        $this->player->addTitle(TitleEnum::COM_MANAGER);
    }

    private function givenPlayerIsDirty(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::DIRTY,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenLinkWithSolIsEstablished(): void
    {
        $linkWithSol = $this->linkWithSolRepository->findByDaedalusIdOrThrow($this->daedalus->getId());
        $linkWithSol->establish();
    }

    private function givenRebelBaseIsContacting(RebelBaseEnum $rebelBaseEnum, FunctionalTester $I): void
    {
        $config = $I->grabEntityFromRepository(RebelBaseConfig::class, ['key' => $rebelBaseEnum->toString() . '_default']);
        $rebelBase = new RebelBase(
            rebelBaseConfig: $config,
            daedalusId: $this->daedalus->getId(),
            isContacting: true,
        );
        $this->rebelBaseRepository->save($rebelBase);
    }

    private function givenRebelBaseSignalIsAt(RebelBaseEnum $rebelBaseEnum, int $signal): void
    {
        $rebelBase = $this->rebelBaseRepository->findByDaedalusIdAndNameOrThrow(
            daedalusId: $this->daedalus->getId(),
            name: $rebelBaseEnum,
        );
        $rebelBase->increaseDecodingProgress($signal);
        $this->rebelBaseRepository->save($rebelBase);
    }

    private function givenNonContactingRebelBase(RebelBaseEnum $rebelBaseEnum, FunctionalTester $I): void
    {
        $config = $I->grabEntityFromRepository(RebelBaseConfig::class, ['key' => $rebelBaseEnum->toString() . '_default']);
        $rebelBase = new RebelBase(
            rebelBaseConfig: $config,
            daedalusId: $this->daedalus->getId(),
        );
        $this->rebelBaseRepository->save($rebelBase);
    }

    private function givenChunHasMoralePoints(int $points): void
    {
        $this->chun->setMoralPoint($points);
    }

    private function givenKuanTiHasMoralePoints(int $points): void
    {
        $this->kuanTi->setMoralPoint($points);
    }

    private function whenPlayerDecodesRebelSignal(RebelBaseEnum $signal): void
    {
        $this->decodeRebelBase->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->commsCenter,
            player: $this->player,
            target: $this->commsCenter,
            parameters: ['rebel_base' => $signal->toString()],
        );
        $this->decodeRebelBase->execute();
    }

    private function thenChunShouldHaveMoralePoints(int $points, FunctionalTester $I): void
    {
        $I->assertEquals($points, $this->chun->getMoralPoint(), "Chun should have {$points} morale points, but has " . $this->chun->getMoralPoint());
    }

    private function thenKuanTiShouldHaveMoralePoints(int $points, FunctionalTester $I): void
    {
        $I->assertEquals($points, $this->kuanTi->getMoralPoint(), "Kuan Ti should have {$points} morale points, but has " . $this->kuanTi->getMoralPoint());
    }
}
