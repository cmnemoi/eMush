<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Action\Actions\PetCat;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\MushMessageEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PetCatCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private PetCat $petCat;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private PendingStatisticRepositoryInterface $pendingStatisticRepository;
    private GameItem $schrodinger;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::PET_CAT]);
        $this->petCat = $I->grabService(PetCat::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->pendingStatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);

        $this->givenPlayerHasCatInInventory($I);
    }

    public function shouldNotBeExecutableIfPlayerIsGermaphobe(FunctionalTester $I): void
    {
        $this->givenPlayerIsGermaphobe();

        $this->whenPlayerTriesToPetCat();

        $this->thenActionShouldNotBeExecutableBecauseOfGermaphobe($I);
    }

    public function shouldGiveThreeMoralePointsToPlayer(FunctionalTester $I): void
    {
        $this->givenPlayerHasMoralePoints(10);

        $this->whenPlayerPetsCat();

        $this->thenPlayerShouldHaveMoralePoints(13, $I);
    }

    public function shouldPrintPublicLog(FunctionalTester $I): void
    {
        $this->whenPlayerPetsCat();

        $I->seeInRepository(
            RoomLog::class,
            [
                'place' => $this->player->getPlace()->getLogName(),
                'log' => ActionLogEnum::PET_CAT,
            ]
        );
    }

    public function shouldNotGiveMoralePointsIfAlreadyDoneOnce(FunctionalTester $I): void
    {
        $this->givenPlayerHasMoralePoints(10);

        $this->givenPlayerHasAlreadyPettedCat();

        $this->whenPlayerPetsCat();

        $this->thenPlayerShouldHaveMoralePoints(10, $I);
    }

    public function shouldInfectHumanIfCatIsInfected(FunctionalTester $I): void
    {
        $this->givenCatIsInfected($I);

        $this->givenPlayerHasSpores(0);

        $this->actionConfig->setInjuryRate(100);
        $I->flushToDatabase($this->actionConfig);

        $this->whenPlayerPetsCat();

        $this->thenPlayerShouldHaveSpores(1, $I);
    }

    public function shouldNotInfectMushPlayer(FunctionalTester $I): void
    {
        $this->givenCatIsInfected($I);

        $this->givenPlayerHasSpores(0);

        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );

        $this->actionConfig->setInjuryRate(100);
        $I->flushToDatabase($this->actionConfig);

        $this->whenPlayerPetsCat();

        $this->thenPlayerShouldHaveSpores(0, $I);
    }

    public function shouldPrintLogInMushChannelWhenInfectingPlayer(FunctionalTester $I): void
    {
        $this->givenCatIsInfected($I);

        $this->givenPlayerHasSpores(0);

        $this->actionConfig->setInjuryRate(100);
        $I->flushToDatabase($this->actionConfig);

        $this->whenPlayerPetsCat();

        $I->seeInRepository(
            Message::class,
            [
                'channel' => $this->mushChannel,
                'message' => MushMessageEnum::INFECT_CAT,
            ]
        );
    }

    public function shouldIncrementCatCuddledPendingStatistic(FunctionalTester $I): void
    {
        $this->whenPlayerPetsCat();

        $statistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            StatisticEnum::CAT_CUDDLED,
            $this->player->getUser()->getId(),
            $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
        );

        $I->assertEquals(
            expected: [
                'name' => StatisticEnum::CAT_CUDDLED,
                'userId' => $this->player->getUser()->getId(),
                'closedDaedalusId' => $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId(),
                'count' => 1,
                'isRare' => false,
            ],
            actual: $statistic->toArray()
        );
    }

    private function givenPlayerHasCatInInventory(FunctionalTester $I): void
    {
        $this->schrodinger = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::SCHRODINGER,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenCatIsInfected(FunctionalTester $I): void
    {
        $jinSu = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::JIN_SU);

        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $jinSu,
            tags: [],
            time: new \DateTime(),
        );

        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::CAT_INFECTED,
            holder: $this->schrodinger,
            tags: [],
            time: new \DateTime(),
            target: $jinSu,
        );
    }

    private function givenPlayerIsGermaphobe(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::GERMAPHOBE,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerHasAlreadyPettedCat(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HAS_PETTED_CAT,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerHasMoralePoints(int $moralePoints): void
    {
        $this->player->setMoralPoint($moralePoints);
    }

    private function givenPlayerHasSpores(int $spores): void
    {
        $this->player->setSpores($spores);
    }

    private function whenPlayerTriesToPetCat(): void
    {
        $this->petCat->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->schrodinger,
            player: $this->player,
            target: $this->schrodinger,
        );
    }

    private function whenPlayerPetsCat(): void
    {
        $this->whenPlayerTriesToPetCat();
        $this->petCat->execute();
    }

    private function thenActionShouldNotBeExecutableBecauseOfGermaphobe(FunctionalTester $I): void
    {
        $I->assertEquals(ActionImpossibleCauseEnum::PLAYER_IS_GERMAPHOBIC, $this->petCat->cannotExecuteReason());
    }

    private function thenPlayerShouldHaveMoralePoints(int $expectedMoralePoints, FunctionalTester $I): void
    {
        $I->assertEquals($expectedMoralePoints, $this->player->getMoralPoint());
    }

    private function thenPlayerShouldHaveSpores(int $expectedSpores, FunctionalTester $I): void
    {
        $I->assertEquals($expectedSpores, $this->player->getSpores());
    }
}
