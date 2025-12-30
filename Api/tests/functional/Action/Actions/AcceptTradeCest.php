<?php

declare(strict_types=1);

namespace Mush\Action\Tests\Functional\Actions;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Action\Actions\AcceptTrade;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Communications\Entity\Trade;
use Mush\Communications\Factory\TradeFactory;
use Mush\Communications\Repository\TradeRepositoryInterface;
use Mush\Daedalus\Entity\DaedalusProjectsStatistics;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Exception\GameException;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Repository\HunterRepositoryInterface;
use Mush\Hunter\Service\CreateHunterService;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class AcceptTradeCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private AcceptTrade $acceptTrade;

    private CreateHunterService $createHunter;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private HunterRepositoryInterface $hunterRepository;
    private PendingStatisticRepositoryInterface $pendingStatisticRepository;
    private StatusServiceInterface $statusService;
    private TradeRepositoryInterface $tradeRepository;

    private GameEquipment $commsCenter;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::ACCEPT_TRADE]);
        $this->acceptTrade = $I->grabService(AcceptTrade::class);

        $this->createHunter = $I->grabService(CreateHunterService::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->hunterRepository = $I->grabService(HunterRepositoryInterface::class);
        $this->pendingStatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->tradeRepository = $I->grabService(TradeRepositoryInterface::class);

        $this->commsCenter = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::COMMUNICATION_CENTER,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        $this->givenStorages($I);
    }

    public function shouldNotBeVisibleIfPlayerIsNotFocusedOnCommsCenter(FunctionalTester $I): void
    {
        $this->whenPlayerTriesToAcceptTrade();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldNotBeVisibleIfHuntersAreAttackingDaedalus(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();

        $this->givenHunterIsAttackingDaedalus(HunterEnum::HUNTER);

        $this->whenPlayerTriesToAcceptTrade();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldNotBeExecutableIfPlayerIsNotTheAliveCommsManager(FunctionalTester $I): void
    {
        $this->givenOtherPlayerIsCommsManager();
        $this->givenPlayerIsFocusedOnCommsCenter();

        $this->whenPlayerTriesToAcceptTrade();

        $this->thenActionShouldNotBeExecutableWithMessage($I, ActionImpossibleCauseEnum::COMS_NOT_OFFICER);
    }

    public function shouldBeVisibleIfAsteroidIsAttackingDaedalus(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenHunterIsAttackingDaedalus(HunterEnum::ASTEROID);

        $this->whenPlayerTriesToAcceptTrade();

        $this->thenActionShouldBeVisible($I);
    }

    public function shouldBeVisibleIfTransportIsAttackingDaedalus(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $this->givenHunterIsAttackingDaedalus(HunterEnum::TRANSPORT);

        $this->whenPlayerTriesToAcceptTrade();

        $this->thenActionShouldBeVisible($I);
    }

    public function shouldThrowExceptionIfTradeTermsAreNotMet(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $trade = $this->givenForestDealTrade(requiredHydropot: 1, offeredOxygen: 10);

        $I->expectThrowable(GameException::class, function () use ($trade) {
            $this->whenPlayerAcceptsTrade(tradeOptionId: $trade->getTradeOptions()->first()->getId());
        });
    }

    public function shouldBeExecutableIfThereIsNoAliveCommsManager(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();

        $this->whenPlayerTriesToAcceptTrade();

        $this->thenActionShouldBeExecutable($I);
    }

    public function shouldConsumeRequiredTradeOptionAssets(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $trade = $this->givenForestDealTrade(requiredHydropot: 1, offeredOxygen: 10);
        $this->givenItemInPlace(ItemEnum::HYDROPOT, RoomEnum::FRONT_STORAGE);

        $this->whenPlayerAcceptsTrade(tradeOptionId: $trade->getTradeOptions()->first()->getId());

        $frontStorage = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::FRONT_STORAGE);
        $I->assertFalse($frontStorage->hasEquipmentByName(ItemEnum::HYDROPOT), 'Hydropot in front storage should be consumed');
    }

    public function shouldRemoveProjectsFromDaedalusProjectsStatistics(FunctionalTester $I): void
    {   // given it has a new DaedalusProjectsStatistics
        $daedalusProjectsStatistics = new DaedalusProjectsStatistics();
        $this->daedalus->getDaedalusInfo()->setDaedalusProjectsStatistics($daedalusProjectsStatistics);

        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();

        $trade = $this->givenTestProjectTrade(requiredProjects: 1, offeredOxygen: 10);

        // given a project is finished
        $this->finishProject(
            $this->daedalus->getProjectByName(ProjectName::ARMOUR_CORRIDOR),
            $this->player,
            $I
        );

        $this->whenPlayerAcceptsTrade(tradeOptionId: $trade->getTradeOptions()->first()->getId());

        $I->assertTrue(\count($daedalusProjectsStatistics->getNeronProjectsCompleted()) === 0, 'NeronProjectsCompleted should be empty');
    }

    public function shouldGiveOfferedTradeOptionAssets(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $trade = $this->givenForestDealTrade(requiredHydropot: 1, offeredOxygen: 10);
        $this->givenItemInPlace(ItemEnum::HYDROPOT, RoomEnum::FRONT_STORAGE);

        $this->whenPlayerAcceptsTrade(tradeOptionId: $trade->getTradeOptions()->first()->getId());

        $I->assertCount(10, $this->player->getPlace()->getAllEquipmentsByName(ItemEnum::OXYGEN_CAPSULE), 'Player room should have 10 oxygen capsules');
    }

    public function shouldCreatePublicLogForCreatedItems(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $trade = $this->givenForestDealTrade(requiredHydropot: 1, offeredOxygen: 10);
        $this->givenItemInPlace(ItemEnum::HYDROPOT, RoomEnum::FRONT_STORAGE);

        $this->whenPlayerAcceptsTrade(tradeOptionId: $trade->getTradeOptions()->first()->getId());

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Une cargaison de 10 **Capsules d\'oxygène** a été téléportée sur le pont ! Sympa la technologie alien !',
            actualRoomLogDto: new RoomLogDto(
                player: $this->player,
                log: LogEnum::TRADE_ASSETS_CREATED,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false,
            ),
            I: $I,
        );
    }

    public function shouldDeleteTransport(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $trade = $this->givenForestDealTrade(requiredHydropot: 1, offeredOxygen: 10);
        $this->givenItemInPlace(ItemEnum::HYDROPOT, RoomEnum::FRONT_STORAGE);

        $this->whenPlayerAcceptsTrade(tradeOptionId: $trade->getTradeOptions()->first()->getId());

        $I->expectThrowable(new \RuntimeException("Hunter not found for id {$trade->getTransportId()}"), function () use ($trade) {
            $this->hunterRepository->findByIdOrThrow($trade->getTransportId());
        });
    }

    public function shouldDeleteTrade(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $trade = $this->givenForestDealTrade(requiredHydropot: 1, offeredOxygen: 10);
        $this->givenItemInPlace(ItemEnum::HYDROPOT, RoomEnum::FRONT_STORAGE);

        $this->whenPlayerAcceptsTrade(tradeOptionId: $trade->getTradeOptions()->first()->getId());

        $I->assertEmpty($this->tradeRepository->findAllByDaedalusId($this->daedalus->getId()));
    }

    public function shouldNotCreateMerchantLeaveNeronAnnouncement(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $trade = $this->givenForestDealTrade(requiredHydropot: 1, offeredOxygen: 10);
        $this->givenItemInPlace(ItemEnum::HYDROPOT, RoomEnum::FRONT_STORAGE);

        $this->whenPlayerAcceptsTrade(tradeOptionId: $trade->getTradeOptions()->first()->getId());

        $I->dontSeeInRepository(
            entity: Message::class,
            params: [
                'neron' => $this->daedalus->getNeron(),
                'message' => NeronMessageEnum::MERCHANT_LEAVE,
            ],
        );
    }

    public function shouldCreateMerchantExchangeNeronAnnouncement(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $trade = $this->givenForestDealTrade(requiredHydropot: 1, offeredOxygen: 10);
        $this->givenItemInPlace(ItemEnum::HYDROPOT, RoomEnum::FRONT_STORAGE);

        $this->whenPlayerAcceptsTrade(tradeOptionId: $trade->getTradeOptions()->first()->getId());

        $I->seeInRepository(
            entity: Message::class,
            params: [
                'neron' => $this->daedalus->getNeron(),
                'message' => NeronMessageEnum::MERCHANT_EXCHANGE,
            ],
        );
    }

    public function shouldGiveAndieTriumph(FunctionalTester $I): void
    {
        $andie = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::ANDIE);
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();
        $trade = $this->givenForestDealTrade(requiredHydropot: 1, offeredOxygen: 10);
        $this->givenItemInPlace(ItemEnum::HYDROPOT, RoomEnum::FRONT_STORAGE);

        $this->whenPlayerAcceptsTrade(tradeOptionId: $trade->getTradeOptions()->first()->getId());

        $I->assertEquals(4, $andie->getTriumph());
        $I->assertEquals(0, $this->player->getTriumph());
    }

    public function shouldExchangePlayerIfInactive(FunctionalTester $I): void
    {
        $this->givenPlayerIsCommsManager();
        $this->givenPlayerIsFocusedOnCommsCenter();

        $trade = $this->givenHumanFuelDealTrade(1, 1);
        $this->givenKuanIsInactiveInStorage();

        $this->whenPlayerAcceptsTrade($trade->getTradeOptions()->first()->getId());

        $this->thenKuanTiShouldBeDead($I);
    }

    public function shouldNotExchangePlayerIfStrawmanInStorage(FunctionalTester $I): void
    {
        $this->givenPlayerIsCommsManager();
        $this->givenPlayerIsFocusedOnCommsCenter();

        $trade = $this->givenHumanFuelDealTrade(5, 1);
        $this->givenKuanIsInactiveInStorage();
        $this->givenStrawnManIsInStorage();
        $this->givenStrawnManIsInStorage();
        $this->givenStrawnManIsInStorage();
        $this->givenStrawnManIsInStorage();
        $this->givenStrawnManIsInStorage();

        $this->whenPlayerAcceptsTrade($trade->getTradeOptions()->first()->getId());

        $this->thenKuanTiShouldBeAlive($I);
        $this->thenStrawmanShouldNotBeInStorage($I);
    }

    public function shouldExchangeStrawman(FunctionalTester $I): void
    {
        $this->givenPlayerIsCommsManager();
        $this->givenPlayerIsFocusedOnCommsCenter();

        $trade = $this->givenHumanFuelDealTrade(1, 1);
        $this->givenStrawnManIsInStorage();

        $this->whenPlayerAcceptsTrade($trade->getTradeOptions()->first()->getId());

        $this->thenStrawmanShouldNotBeInStorage($I);
    }

    public function shouldExchangeBothPlayerAndStrawman(FunctionalTester $I): void
    {
        $this->givenPlayerIsCommsManager();
        $this->givenPlayerIsFocusedOnCommsCenter();

        $trade = $this->givenHumanFuelDealTrade(2, 1);
        $this->givenKuanIsInactiveInStorage();
        $this->givenStrawnManIsInStorage();

        $this->whenPlayerAcceptsTrade($trade->getTradeOptions()->first()->getId());

        $this->thenKuanTiShouldBeDead($I);
        $this->thenStrawmanShouldNotBeInStorage($I);
    }

    public function shouldNotAdvanceProjectsCompletePendingStatistic(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCommsCenter();
        $this->givenPlayerIsCommsManager();

        $trade = $this->givenTestProjectExchangeTrade(requiredProjects: 1, offeredProjects: 2);

        // given a project is finished
        $this->finishProject(
            $this->daedalus->getProjectByName(ProjectName::ARMOUR_CORRIDOR),
            $this->player2,
            $I
        );

        // setup projects which do not need specific room to exist to avoid errors
        $this->daedalus
            ->getAllAvailableProjects()
            ->filter(static fn (Project $project) => !\in_array($project->getName(), [ProjectName::FIRE_SENSOR->toString(), ProjectName::DOOR_SENSOR->toString()], true))
            ->map(static fn (Project $project) => $project->unpropose());

        $this->whenPlayerAcceptsTrade(tradeOptionId: $trade->getTradeOptions()->first()->getId());

        $I->assertNull(
            $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
                name: StatisticEnum::PROJECT_COMPLETE,
                userId: $this->player->getUser()->getId(),
                closedDaedalusId: $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
            )
        );
    }

    private function givenStorages(FunctionalTester $I): void
    {
        foreach (RoomEnum::getStorages() as $storage) {
            $this->createExtraPlace($storage, $I, $this->daedalus);
        }
    }

    private function givenHunterIsAttackingDaedalus(string $hunter): void
    {
        $this->createHunter->execute($hunter, $this->daedalus->getId());
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

    private function givenOtherPlayerIsCommsManager(): void
    {
        $this->player2->addTitle(TitleEnum::COM_MANAGER);
    }

    private function givenForestDealTrade(int $requiredHydropot, int $offeredOxygen): Trade
    {
        $transport = new Hunter(
            hunterConfig: $this->daedalus->getGameConfig()->getHunterConfigs()->getByNameOrThrow(HunterEnum::TRANSPORT),
            daedalus: $this->daedalus,
        );
        $this->hunterRepository->save($transport);

        $trade = TradeFactory::createForestDealTrade(
            requiredHydropot: $requiredHydropot,
            offeredOxygen: $offeredOxygen,
            transportId: $transport->getId(),
        );
        $this->tradeRepository->save($trade);

        return $trade;
    }

    private function givenTestProjectTrade(int $requiredProjects, int $offeredOxygen): Trade
    {
        $transport = new Hunter(
            hunterConfig: $this->daedalus->getGameConfig()->getHunterConfigs()->getByNameOrThrow(HunterEnum::TRANSPORT),
            daedalus: $this->daedalus,
        );
        $this->hunterRepository->save($transport);

        $trade = TradeFactory::createProjectTestTrade(
            requiredProjects: $requiredProjects,
            offeredOxygen: $offeredOxygen,
            transportId: $transport->getId(),
        );
        $this->tradeRepository->save($trade);

        return $trade;
    }

    private function givenTestProjectExchangeTrade(int $requiredProjects, int $offeredProjects): Trade
    {
        $transport = new Hunter(
            hunterConfig: $this->daedalus->getGameConfig()->getHunterConfigs()->getByNameOrThrow(HunterEnum::TRANSPORT),
            daedalus: $this->daedalus,
        );
        $this->hunterRepository->save($transport);

        $trade = TradeFactory::createProjectExchangeTestTrade(
            requiredProjects: $requiredProjects,
            offeredProjects: $offeredProjects,
            transportId: $transport->getId(),
        );
        $this->tradeRepository->save($trade);

        return $trade;
    }

    private function givenHumanFuelDealTrade(int $humansRequired, int $offeredFuel): Trade
    {
        $transport = new Hunter(
            hunterConfig: $this->daedalus->getGameConfig()->getHunterConfigs()->getByNameOrThrow(HunterEnum::TRANSPORT),
            daedalus: $this->daedalus,
        );
        $this->hunterRepository->save($transport);

        $trade = TradeFactory::createHumanFuelTestTrade(
            humansRequired: $humansRequired,
            offeredFuel: $offeredFuel,
            transportId: $transport->getId(),
        );
        $this->tradeRepository->save($trade);

        return $trade;
    }

    private function givenItemInPlace(string $item, string $room): void
    {
        $place = $this->daedalus->getPlaceByNameOrThrow($room);
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $item,
            equipmentHolder: $place,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function whenPlayerTriesToAcceptTrade(): void
    {
        $this->acceptTrade->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->commsCenter,
            player: $this->player,
            target: $this->commsCenter,
        );
    }

    private function whenPlayerAcceptsTrade(int $tradeOptionId): void
    {
        $this->acceptTrade->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->commsCenter,
            player: $this->player,
            target: $this->commsCenter,
            parameters: [
                'tradeOptionId' => $tradeOptionId,
            ],
        );

        $this->acceptTrade->execute();
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->acceptTrade->isVisible(), 'Action should not be visible');
    }

    private function thenActionShouldNotBeExecutableWithMessage(FunctionalTester $I, string $cause): void
    {
        $I->assertEquals($cause, $this->acceptTrade->cannotExecuteReason(), "Action should not be executable with cause: {$cause}");
    }

    private function thenActionShouldBeVisible(FunctionalTester $I): void
    {
        $I->assertTrue($this->acceptTrade->isVisible(), 'Action should be visible');
    }

    private function thenActionShouldBeExecutable(FunctionalTester $I): void
    {
        $I->assertNull($this->acceptTrade->cannotExecuteReason(), 'Action should be executable');
    }

    private function givenKuanIsInactiveInStorage(): void
    {
        $this->kuanTi->setPlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::FRONT_STORAGE));
        $this->statusService->createStatusFromName(
            PlayerStatusEnum::HIGHLY_INACTIVE,
            $this->kuanTi,
            [],
            new \DateTime()
        );
    }

    private function givenStrawnManIsInStorage(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            ItemEnum::STRAWMAN,
            $this->daedalus->getPlaceByNameOrThrow(RoomEnum::FRONT_STORAGE),
            [],
            new \DateTime()
        );
    }

    private function thenKuanTiShouldBeDead(FunctionalTester $I): void
    {
        $I->assertTrue($this->kuanTi->isDead());
    }

    private function thenKuanTiShouldBeAlive(FunctionalTester $I): void
    {
        $I->assertTrue($this->kuanTi->isAlive());
    }

    private function thenStrawmanShouldNotBeInStorage(FunctionalTester $I): void
    {
        $I->assertFalse($this->daedalus->getPlaceByNameOrThrow(RoomEnum::FRONT_STORAGE)->hasEquipmentByName(ItemEnum::STRAWMAN));
    }
}
