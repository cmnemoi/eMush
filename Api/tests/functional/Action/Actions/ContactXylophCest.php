<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\ContactXyloph;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Communications\Entity\LinkWithSol;
use Mush\Communications\Entity\XylophConfig;
use Mush\Communications\Entity\XylophEntry;
use Mush\Communications\Enum\XylophEnum;
use Mush\Communications\Repository\LinkWithSolRepositoryInterface;
use Mush\Communications\Repository\XylophRepositoryInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\TitleEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ContactXylophCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private ContactXyloph $contactXyloph;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private LinkWithSolRepositoryInterface $linkWithSolRepository;
    private XylophRepositoryInterface $xylophRepository;
    private StatusServiceInterface $statusService;

    private GameEquipment $commsCenter;
    private GameEquipment $antenna;
    private LinkWithSol $linkWithSol;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::CONTACT_XYLOPH]);
        $this->contactXyloph = $I->grabService(ContactXyloph::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->linkWithSolRepository = $I->grabService(LinkWithSolRepositoryInterface::class);
        $this->xylophRepository = $I->grabService(XylophRepositoryInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenCommsCenterInRoom();
    }

    public function shouldNotBeVisibleIfPlayerIsNotFocusedOnCommsCenter(FunctionalTester $I): void
    {
        $this->whenPlayerTriesToContactXyloph();

        $this->thenActionIsNotVisible($I);
    }

    public function shouldNotBeExecutableIfPlayerIsNotCommsManager(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenLinkWithSolIsEstablished();

        $this->whenPlayerTriesToContactXyloph();

        $this->thenActionIsNotExecutableWithMessage(ActionImpossibleCauseEnum::COMS_NOT_OFFICER, $I);
    }

    public function shouldNotBeExecutableIfLinkWithSolIsNotEstablished(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();

        $this->whenPlayerTriesToContactXyloph();

        $this->thenActionIsNotExecutableWithMessage(ActionImpossibleCauseEnum::LINK_WITH_SOL_NOT_ESTABLISHED, $I);
    }

    public function shouldNotBeExecutableIfPlayerIsDirty(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();
        $this->givenPlayerIsDirty();

        $this->whenPlayerTriesToContactXyloph();

        $this->thenActionIsNotExecutableWithMessage(ActionImpossibleCauseEnum::DIRTY_RESTRICTION, $I);
    }

    public function shouldNotBeExecutableIfThereIsNoUndecodedXyloph(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();

        $this->whenPlayerTriesToContactXyloph();

        $this->thenActionIsNotExecutableWithMessage(ActionImpossibleCauseEnum::NO_XYLOPH_LEFT, $I);
    }

    public function shouldSuccessfullyContactXylophWhenAllRequirementsAreMet(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();

        $this->givenThereIsXylophAvailable(XylophEnum::NOTHING, $I);

        $this->whenPlayerContactsXyloph();
        $this->thenXylophDatabaseShouldBeDecoded(XylophEnum::NOTHING, $I);
    }

    public function shouldConsumeThreeActionPointsIfAntennaIsBroken(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();

        $this->givenAnAntennaInDaedalus();
        $this->givenAntennaIsBroken();

        $this->givenPlayerHasActionPoints(10);
        $this->givenThereIsXylophAvailable(XylophEnum::NOTHING, $I);

        $this->whenPlayerContactsXyloph();

        $this->thenPlayerShouldHaveActionPoints(7, $I);
    }

    public function shouldConsumeTwoActionPointsWithFunctionalAntenna(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();

        $this->givenAnAntennaInDaedalus();

        $this->givenPlayerHasActionPoints(10);
        $this->givenThereIsXylophAvailable(XylophEnum::NOTHING, $I);

        $this->whenPlayerContactsXyloph();

        $this->thenPlayerShouldHaveActionPoints(8, $I);
    }

    public function shouldConsumeZeroActionPointsWhenITPointsAvailable(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();
        $this->givenPlayerIsITExpert($I);

        $this->givenAnAntennaInDaedalus();
        $this->givenAntennaIsBroken();

        $this->givenPlayerHasActionPoints(10);
        $this->givenThereIsXylophAvailable(XylophEnum::NOTHING, $I);

        $this->whenPlayerContactsXyloph();

        $this->thenPlayerShouldHaveActionPoints(10, $I);
    }

    public function shouldLoseLinkOnSnowXyloph(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();

        $this->givenThereIsXylophAvailable(XylophEnum::SNOW, $I);

        $this->whenPlayerContactsXyloph();

        $this->thenLinkWithSolShouldBeBroken($I);
    }

    public function shouldDecreaseLinkStrengthToZeroWhenLessThanTwentyOnMagnetiteXyloph(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();
        $this->givenLinkStrengthIsIncreasedBy(12);

        $this->givenThereIsXylophAvailable(XylophEnum::MAGNETITE, $I);

        $this->whenPlayerContactsXyloph();

        $this->thenLinkStrengthShouldBe(0, $I);
        $this->thenLinkWithSolShouldBeBroken($I);
    }

    public function shouldDecreaseLinkStrengthByExactlyTwentyOnMagnetiteXyloph(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();
        $this->givenLinkStrengthIsIncreasedBy(32);

        $this->givenThereIsXylophAvailable(XylophEnum::MAGNETITE, $I);

        $this->whenPlayerContactsXyloph();

        $this->thenLinkStrengthShouldBe(12, $I);
        $this->thenLinkWithSolShouldBeBroken($I);
    }

    public function shouldGiveMushGenomeDiskOnDiskXyloph(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();

        $this->givenThereIsXylophAvailable(XylophEnum::DISK, $I);

        $this->whenPlayerContactsXyloph();

        $this->thenDiskShouldBeInRoom($I);
    }

    public function shouldGiveMostWeightedXyloph(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenLinkWithSolIsEstablished();

        $this->givenThereIsWeightedXylophAvailable(XylophEnum::NOTHING, 1, $I);
        $this->givenThereIsWeightedXylophAvailable(XylophEnum::SNOW, 0, $I);
        $this->givenThereIsWeightedXylophAvailable(XylophEnum::DISK, 0, $I);

        $this->whenPlayerContactsXyloph();

        $this->thenXylophDatabaseShouldBeDecoded(XylophEnum::NOTHING, $I);
        $this->thenXylophDatabaseShouldNotBeDecoded(XylophEnum::SNOW, $I);
        $this->thenXylophDatabaseShouldNotBeDecoded(XylophEnum::DISK, $I);
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

    private function givenAnAntennaInDaedalus(): void
    {
        $this->antenna = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::ANTENNA,
            equipmentHolder: $this->daedalus->getPlaceByNameOrThrow(RoomEnum::SPACE),
            reasons: [],
            time: new \DateTime(),
        );
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
        $this->linkWithSol = $this->linkWithSolRepository->findByDaedalusIdOrThrow($this->daedalus->getId());
        $this->linkWithSol->establish();
    }

    private function givenLinkStrengthIsIncreasedBy(int $quantity): void
    {
        $this->linkWithSol->increaseStrength($quantity);
    }

    private function givenThereIsXylophAvailable(XylophEnum $xylophEnum, FunctionalTester $I): void
    {
        $config = $I->grabEntityFromRepository(XylophConfig::class, ['key' => $xylophEnum->toString() . '_default']);
        $xylophEntry = new XylophEntry(
            xylophConfig: $config,
            daedalusId: $this->daedalus->getId(),
        );
        $this->xylophRepository->save($xylophEntry);
    }

    private function givenThereIsWeightedXylophAvailable(XylophEnum $xylophEnum, int $weight, FunctionalTester $I): void
    {
        $config = $I->grabEntityFromRepository(XylophConfig::class, ['key' => $xylophEnum->toString() . '_default']);
        $xylophEntry = new XylophEntry(
            xylophConfig: $config,
            daedalusId: $this->daedalus->getId(),
        );
        $xylophEntry->setWeight($weight);
        $this->xylophRepository->save($xylophEntry);
    }

    private function givenPlayerHasActionPoints(int $points): void
    {
        $this->player->setActionPoint($points);
    }

    private function givenPlayerIsITExpert(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::IT_EXPERT, $I, $this->player);
    }

    private function whenPlayerTriesToContactXyloph(): void
    {
        $this->contactXyloph->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->commsCenter,
            player: $this->player,
            target: $this->commsCenter,
        );
    }

    private function whenPlayerContactsXyloph(): void
    {
        $this->contactXyloph->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->commsCenter,
            player: $this->player,
            target: $this->commsCenter,
        );
        $this->contactXyloph->execute();
    }

    private function thenActionIsNotVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->contactXyloph->isVisible(), 'Action should not be visible');
    }

    private function thenActionIsNotExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->contactXyloph->cannotExecuteReason(), "Action should not be executable with message: {$message}");
    }

    private function thenPlayerShouldHaveActionPoints(int $points, FunctionalTester $I): void
    {
        $I->assertEquals($points, $this->player->getActionPoint(), "Player should have {$points} action points, but has " . $this->player->getActionPoint());
    }

    private function thenXylophDatabaseShouldBeDecoded(XylophEnum $xylophEnum, FunctionalTester $I): void
    {
        $xylophEntry = $this->xylophRepository->findByDaedalusIdAndNameOrThrow($this->daedalus->getId(), $xylophEnum->value);
        $I->assertTrue($xylophEntry->isDecoded());
    }

    private function thenXylophDatabaseShouldNotBeDecoded(XylophEnum $xylophEnum, FunctionalTester $I): void
    {
        $xylophEntry = $this->xylophRepository->findByDaedalusIdAndNameOrThrow($this->daedalus->getId(), $xylophEnum->value);
        $I->assertFalse($xylophEntry->isDecoded());
    }

    private function thenLinkWithSolShouldBeBroken(FunctionalTester $I): void
    {
        $I->assertTrue($this->linkWithSol->isNotEstablished());
    }

    private function thenLinkStrengthShouldBe(int $quantity, FunctionalTester $I): void
    {
        $I->assertEquals($quantity, $this->linkWithSol->getStrength());
    }

    private function thenDiskShouldBeInRoom(FunctionalTester $I): void
    {
        $I->assertCount(1, $this->player->getPlace()->getItems());
        $I->assertNotEmpty($this->player->getPlace()->getEquipmentByName(ItemEnum::MUSH_GENOME_DISK));
    }
}
