<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Action\Actions\Consume;
use Mush\Action\Actions\DecodeRebelSignal;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\RebelBaseConfig;
use Mush\Communications\Entity\XylophConfig;
use Mush\Communications\Entity\XylophEntry;
use Mush\Communications\Enum\RebelBaseEnum;
use Mush\Communications\Enum\XylophEnum;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Communications\Repository\XylophRepositoryInterface;
use Mush\Communications\Service\DecodeXylophDatabaseServiceInterface;
use Mush\Daedalus\Entity\DaedalusStatistics;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Exception\GameException;
use Mush\Skill\Enum\SkillEnum;
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

    private DecodeXylophDatabaseServiceInterface $decodeXylophDatabaseService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private LinkWithSolRepositoryInterface $linkWithSolRepository;
    private RebelBaseRepositoryInterface $rebelBaseRepository;
    private StatusServiceInterface $statusService;
    private XylophRepositoryInterface $xylophRepository;

    private GameEquipment $commsCenter;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::DECODE_REBEL_SIGNAL]);
        $this->decodeRebelBase = $I->grabService(DecodeRebelSignal::class);

        $this->decodeXylophDatabaseService = $I->grabService(DecodeXylophDatabaseServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->linkWithSolRepository = $I->grabService(LinkWithSolRepositoryInterface::class);
        $this->rebelBaseRepository = $I->grabService(RebelBaseRepositoryInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->xylophRepository = $I->grabService(XylophRepositoryInterface::class);

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

    public function shouldMarkRebelBaseAsNonContactingOnSuccess(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();
        $this->givenRebelBaseIsContacting(RebelBaseEnum::WOLF, $I);
        $this->givenRebelBaseSignalIsAt(RebelBaseEnum::WOLF, 99);

        $this->whenPlayerDecodesRebelSignal(RebelBaseEnum::WOLF);

        $this->thenRebelBaseShouldNotContact(RebelBaseEnum::WOLF, $I);
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

    #[DataProvider('rationTypesProvider')]
    public function siriusShouldAddPlusOneActionPointForRationsOnSuccess(FunctionalTester $I, Example $rationExample): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();
        $this->givenRebelBaseIsContacting(RebelBaseEnum::SIRIUS, $I);
        $this->givenRebelBaseSignalIsAt(RebelBaseEnum::SIRIUS, 99);
        $this->givenPlayerDecodesRebelSignal(RebelBaseEnum::SIRIUS);
        $this->givenPlayerHasFood($rationExample['ration']);
        $this->givenPlayerHasActionPoints(0);

        $this->whenPlayerConsumesFood($rationExample['ration'], $I);

        $this->thenPlayerShouldHaveActionPoints(5, $I);
    }

    public function siriusDoesNotAddPlusOneActionPointForBananasOnSuccess(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();
        $this->givenRebelBaseIsContacting(RebelBaseEnum::SIRIUS, $I);
        $this->givenRebelBaseSignalIsAt(RebelBaseEnum::SIRIUS, 99);
        $this->givenPlayerDecodesRebelSignal(RebelBaseEnum::SIRIUS);
        $this->givenPlayerHasFood(GameFruitEnum::BANANA);
        $this->givenPlayerHasActionPoints(0);

        $this->whenPlayerConsumesFood(GameFruitEnum::BANANA, $I);

        $this->thenPlayerShouldHaveActionPoints(1, $I);
    }

    public function luytenCetiShouldCreateBrainsyncStatusOnSuccess(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();
        $this->givenRebelBaseIsContacting(RebelBaseEnum::LUYTEN_CETI, $I);
        $this->givenRebelBaseSignalIsAt(RebelBaseEnum::LUYTEN_CETI, 99);

        $this->whenPlayerDecodesRebelSignal(RebelBaseEnum::LUYTEN_CETI);

        $this->thenChunShouldHaveBrainsync($I);
        $this->thenKuanTiShouldHaveBrainsync($I);
    }

    public function cygniRebelBaseShouldGiveThreeMoralePointsOnSuccess(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();
        $this->givenRebelBaseIsContacting(RebelBaseEnum::CYGNI, $I);
        $this->givenRebelBaseSignalIsAt(RebelBaseEnum::CYGNI, 99);
        $this->givenChunHasMoralePoints(0);
        $this->givenKuanTiHasMoralePoints(0);

        $this->whenPlayerDecodesRebelSignal(RebelBaseEnum::CYGNI);

        $this->thenChunShouldHaveMoralePoints(3, $I);
        $this->thenKuanTiShouldHaveMoralePoints(3, $I);
    }

    public function wolfShouldGiveEightTriumphPointsOnSuccess(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();
        $this->givenRebelBaseIsContacting(RebelBaseEnum::WOLF, $I);
        $this->givenRebelBaseSignalIsAt(RebelBaseEnum::WOLF, 99);

        $this->chun->setTriumph(0);
        $this->kuanTi->setTriumph(0);

        $this->whenPlayerDecodesRebelSignal(RebelBaseEnum::WOLF);

        $I->assertEquals(8, $this->chun->getTriumph());
        $I->assertEquals(8, $this->kuanTi->getTriumph());
    }

    public function shouldDoubleOutputQuantityOnRebelSkill(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();
        $this->givenRebelBaseIsContacting(RebelBaseEnum::WOLF, $I);

        $initialSignalMaxEfficiency = $this->decodeRebelSignalOutputQuantity();

        $this->givenPlayerIsRebel($I);

        $this->thenMaxEfficiencyShouldBeDoubleTo($initialSignalMaxEfficiency, $I);
    }

    public function shouldDoubleOutputQuantityOnKivancDecoded(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();
        $this->givenRebelBaseIsContacting(RebelBaseEnum::WOLF, $I);

        $initialSignalMaxEfficiency = $this->decodeRebelSignalOutputQuantity();

        $this->givenHasContactedKivanc($I);

        $this->thenMaxEfficiencyShouldBeDoubleTo($initialSignalMaxEfficiency, $I);
    }

    public function shouldQuadrupleOutputQuantityWhenRebelHasContactedKivanc(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();
        $this->givenRebelBaseIsContacting(RebelBaseEnum::WOLF, $I);

        $initialSignalMaxEfficiency = $this->decodeRebelSignalOutputQuantity();

        $this->givenPlayerIsRebel($I);
        $this->givenHasContactedKivanc($I);

        $this->thenMaxEfficiencyShouldBeQuadrupleTo($initialSignalMaxEfficiency, $I);
    }

    public function shouldIncrementRebelBasesCounterOnSuccess(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();
        $this->givenRebelBaseIsContacting(RebelBaseEnum::WOLF, $I);
        $this->givenRebelBaseSignalIsAt(RebelBaseEnum::WOLF, 99);
        // given the rebel bases counter is set to 0
        $this->daedalus->getDaedalusInfo()->setDaedalusStatistics(new DaedalusStatistics(rebelBasesContacted: 0));

        $this->whenPlayerDecodesRebelSignal(RebelBaseEnum::WOLF);

        // then the rebel bases counter should be incremented to 1.
        $I->assertEquals(1, $this->daedalus->getDaedalusInfo()->getDaedalusStatistics()->getRebelBasesContacted(), 'rebelBasesContacted should be 1.');
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
            config: $config,
            daedalusId: $this->daedalus->getId(),
            contactStartDate: new \DateTimeImmutable(),
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
            config: $config,
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

    private function givenPlayerHasFood(string $food): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $food,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerHasActionPoints(int $points): void
    {
        $this->player->setActionPoint($points);
    }

    private function givenPlayerDecodesRebelSignal(RebelBaseEnum $signal): void
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

    private function givenPlayerIsRebel(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::REBEL, $I, $this->player);
    }

    private function givenHasContactedKivanc(FunctionalTester $I): void
    {
        $config = $I->grabEntityFromRepository(XylophConfig::class, ['key' => XylophEnum::KIVANC->toString() . '_default']);
        $xylophEntry = new XylophEntry(
            xylophConfig: $config,
            daedalusId: $this->daedalus->getId(),
        );
        $this->xylophRepository->save($xylophEntry);

        $this->decodeXylophDatabaseService->execute(
            xylophEntry: $xylophEntry,
            player: $this->player,
        );
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

    private function whenPlayerConsumesFood(string $food, FunctionalTester $I): void
    {
        $actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::CONSUME->toString()]);
        $action = $I->grabService(Consume::class);
        $action->loadParameters(
            actionConfig: $actionConfig,
            actionProvider: $this->player->getEquipmentByNameOrThrow($food),
            player: $this->player,
            target: $this->player->getEquipmentByNameOrThrow($food),
        );
        $action->execute();
    }

    private function thenChunShouldHaveMoralePoints(int $points, FunctionalTester $I): void
    {
        $I->assertEquals($points, $this->chun->getMoralPoint(), "Chun should have {$points} morale points, but has " . $this->chun->getMoralPoint());
    }

    private function thenKuanTiShouldHaveMoralePoints(int $points, FunctionalTester $I): void
    {
        $I->assertEquals($points, $this->kuanTi->getMoralPoint(), "Kuan Ti should have {$points} morale points, but has " . $this->kuanTi->getMoralPoint());
    }

    private function thenRebelBaseShouldNotContact(RebelBaseEnum $rebelBaseEnum, FunctionalTester $I): void
    {
        $rebelBase = $this->rebelBaseRepository->findByDaedalusIdAndNameOrThrow(
            daedalusId: $this->daedalus->getId(),
            name: $rebelBaseEnum,
        );

        $I->assertTrue($rebelBase->isNotContacting(), "Rebel base {$rebelBaseEnum->toString()} should not be contacting");
    }

    private function thenPlayerShouldHaveActionPoints(int $points, FunctionalTester $I): void
    {
        $I->assertEquals($points, $this->player->getActionPoint(), "Player should have {$points} action points, but has " . $this->player->getActionPoint());
    }

    private function thenMaxEfficiencyShouldBeDoubleTo(int $initialEfficiency, FunctionalTester $I): void
    {
        $expectedOutput = $initialEfficiency * 2;
        $actualOutput = $this->decodeRebelSignalOutputQuantity();
        $I->assertEquals($expectedOutput, $actualOutput, "Player should have output {$expectedOutput}%, but has {$actualOutput}%");
    }

    private function thenMaxEfficiencyShouldBeQuadrupleTo(int $initialEfficiency, FunctionalTester $I): void
    {
        $expectedOutput = $initialEfficiency * 4;
        $actualOutput = $this->decodeRebelSignalOutputQuantity();
        $I->assertEquals($expectedOutput, $actualOutput, "Player should have output {$expectedOutput}%, but has {$actualOutput}%");
    }

    private function thenChunShouldHaveBrainsync(FunctionalTester $I): void
    {
        $I->assertTrue($this->chun->hasStatus(PlayerStatusEnum::BRAINSYNC));
    }

    private function thenKuanTiShouldHaveBrainsync(FunctionalTester $I): void
    {
        $I->assertTrue($this->kuanTi->hasStatus(PlayerStatusEnum::BRAINSYNC));
    }

    private function rationTypesProvider(): array
    {
        return [
            ['ration' => GameRationEnum::STANDARD_RATION],
            ['ration' => GameRationEnum::COOKED_RATION],
        ];
    }

    private function decodeRebelSignalOutputQuantity(): int
    {
        $this->decodeRebelBase->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->commsCenter,
            player: $this->player,
            target: $this->commsCenter,
        );

        return $this->decodeRebelBase->getOutputQuantity();
    }
}
