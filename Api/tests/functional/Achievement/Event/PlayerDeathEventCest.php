<?php

declare(strict_types=1);

namespace Mush\tests\functional\Achievement\Event;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
use Mush\Action\Actions\Shoot;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlayerDeathEventCest extends AbstractFunctionalTest
{
    private ActionConfig $shootConfig;
    private Shoot $shootAction;

    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PendingStatisticRepositoryInterface $pendingStatisticRepository;
    private PlayerServiceInterface $playerService;
    private StatisticRepositoryInterface $statisticRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->shootConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::SHOOT]);
        $this->shootAction = $I->grabService(Shoot::class);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->pendingStatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
        $this->statisticRepository = $I->grabService(StatisticRepositoryInterface::class);
    }

    public function shouldNotGrantNatamistStatisticOnHumanKillHuman(FunctionalTester $I): void
    {
        $this->givenPlayer2HasOneHealthPoint();
        $this->givenPlayerHasNatamyRifle();

        $this->whenPlayerKillsPlayer2WithNatamyRifle();

        $this->thenPlayerShouldNotHaveNatamistStatistic($I);
    }

    public function shouldGrantNatamistStatisticOnHumanKillMush(FunctionalTester $I): void
    {
        $this->givenPlayer2HasOneHealthPoint();
        $this->givenPlayerHasNatamyRifle();
        $this->givenPlayer2IsMush($I);

        $this->whenPlayerKillsPlayer2WithNatamyRifle();

        $this->thenPlayerShouldHaveNatamistStatistic($I);
    }

    public function shouldNotGrantNatamistStatisticOnMushKillMush(FunctionalTester $I): void
    {
        $this->givenPlayer2HasOneHealthPoint();
        $this->givenPlayerHasNatamyRifle();
        $this->givenPlayerIsMush($I);
        $this->givenPlayer2IsMush($I);

        $this->whenPlayerKillsPlayer2WithNatamyRifle();

        $this->thenPlayerShouldNotHaveNatamistStatistic($I);
    }

    public function shouldNotGrantLastManWhenTheOnlyPlayerDiesOnStartingShip(FunctionalTester $I): void
    {
        $this->givenDaedalusIsMarkedAsStarting();

        $this->whenPlayerDies($this->player2);
        $this->whenPlayerDies($this->player);

        $this->thenLastMandStandingStatisticShouldNotBeAttributed($I);
    }

    public function shouldGrantLastManWhenTheOnlyPlayerDiesOnFullShip(FunctionalTester $I): void
    {
        $this->givenDaedalusIsMarkedAsFull();

        $this->whenPlayerDies($this->player2);
        $this->whenPlayerDies($this->player);

        $this->thenPlayerShouldHaveLastManStandingStatistic($I);
    }

    public function shouldGrantLastManWhenDaedalusIsDestroyedOnStartingShip(FunctionalTester $I): void
    {
        $this->givenDaedalusIsMarkedAsStarting();

        $this->whenDaedalusIsDestroyed($this->player);

        $this->thenLastMandStandingStatisticShouldBeAttributedOnce($I);
    }

    public function shouldGrantLastManWhenDaedalusIsDestroyedOnFullShip(FunctionalTester $I): void
    {
        $this->givenDaedalusIsMarkedAsFull();

        $this->whenDaedalusIsDestroyed($this->player);

        $this->thenLastMandStandingStatisticShouldBeAttributedOnce($I);
    }

    public function shouldNotGrantLastManWhenCrewSurvivesTheEnd(FunctionalTester $I): void
    {
        $this->givenDaedalusIsMarkedAsFull();

        $this->whenDaedalusReturnsToSol($this->player);

        $this->thenLastMandStandingStatisticShouldNotBeAttributed($I);
    }

    public function shouldNotGrantLastCommandantWhenNonCommanderDiesAsLast(FunctionalTester $I): void
    {
        $this->givenDaedalusIsMarkedAsFull();

        $this->whenPlayerDies($this->player2);
        $this->whenPlayerDies($this->player);

        $this->thenPlayerShouldNotHaveLastCommandantStatistic($I);
    }

    public function shouldGrantLastCommandantWhenTheOnlyPlayerDies(FunctionalTester $I): void
    {
        $this->givenDaedalusIsMarkedAsFull();
        $this->givenPlayerIsCommander();

        $this->whenPlayerDies($this->player2);
        $this->whenPlayerDies($this->player); // when commander dies as the last

        $this->thenPlayerShouldHaveLastCommandantStatistic($I);
    }

    public function shouldGrantLastCommandantWhenShipDestroyedWithCommanderIn(FunctionalTester $I): void
    {
        $this->givenDaedalusIsMarkedAsFull();
        $this->givenPlayerIsCommander();

        $this->whenDaedalusIsDestroyed();

        $this->thenPlayerShouldHaveLastCommandantStatistic($I);
    }

    public function shouldNotGrantLastCommandantWhenCommanderSurvivesTheEnd(FunctionalTester $I): void
    {
        $this->givenDaedalusIsMarkedAsFull();
        $this->givenPlayerIsCommander();

        $this->whenDaedalusReturnsToSol();

        $this->thenPlayerShouldNotHaveLastCommandantStatistic($I);
    }

    public function shouldNotGrantLastCommandantWhenCommanderDoesNotSurviveTillTheEnd(FunctionalTester $I): void
    {
        $this->givenDaedalusIsMarkedAsFull();
        $this->givenPlayerIsCommander();

        $this->whenPlayerDies($this->player);
        $this->whenPlayerDies($this->player2); // when non-commander dies as the last

        $this->thenPlayerShouldNotHaveLastCommandantStatistic($I);
    }

    private function givenPlayer2HasOneHealthPoint(): void
    {
        $this->player2->setHealthPoint(1);
    }

    private function givenPlayerHasNatamyRifle(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::NATAMY_RIFLE,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerIsMush(FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->player);
    }

    private function givenPlayer2IsMush(FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->player2);
    }

    private function givenDaedalusIsMarkedAsStarting(): void
    {
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::STARTING);
    }

    private function givenDaedalusIsMarkedAsFull(): void
    {
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::CURRENT);
    }

    private function givenPlayerIsCommander(): void
    {
        $this->playerService->addTitleToPlayer($this->player, TitleEnum::COMMANDER, new \DateTime());
    }

    private function whenPlayerKillsPlayer2WithNatamyRifle(): void
    {
        /** @var GameItem $natamyRifle */
        $natamyRifle = $this->player->getEquipments()->filter(
            static fn (GameItem $item) => $item->getName() === ItemEnum::NATAMY_RIFLE
        )->first();
        $natamyRifle->getWeaponMechanicOrThrow()->setBaseAccuracy(100);

        $this->shootAction->loadParameters(
            actionConfig: $this->shootConfig,
            actionProvider: $natamyRifle,
            player: $this->player,
            target: $this->player2,
        );
        $this->shootAction->execute();
    }

    private function whenPlayerDies(Player $player): void
    {
        $this->playerService->killPlayer(
            player: $player,
            endReason: EndCauseEnum::ASPHYXIA,
        );
    }

    private function whenDaedalusIsDestroyed(): void
    {
        $endDaedalusEvent = new DaedalusEvent(
            $this->daedalus,
            [EndCauseEnum::DAEDALUS_DESTROYED],
            new \DateTime()
        );
        $this->eventService->callEvent($endDaedalusEvent, DaedalusEvent::FINISH_DAEDALUS);
    }

    private function whenDaedalusReturnsToSol(): void
    {
        $endDaedalusEvent = new DaedalusEvent(
            $this->daedalus,
            [EndCauseEnum::SOL_RETURN],
            new \DateTime()
        );
        $this->eventService->callEvent($endDaedalusEvent, DaedalusEvent::FINISH_DAEDALUS);
    }

    private function thenPlayerShouldHaveNatamistStatistic(FunctionalTester $I): void
    {
        $closedDaedalusId = $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId();
        $I->assertEquals(
            expected: [
                'name' => StatisticEnum::NATAMIST,
                'count' => 1,
                'userId' => $this->player->getUser()->getId(),
                'closedDaedalusId' => $closedDaedalusId,
                'isRare' => false,
            ],
            actual: $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
                StatisticEnum::NATAMIST,
                $this->player->getUser()->getId(),
                $closedDaedalusId
            )?->toArray(),
            message: "{$this->player->getLogName()} should have natamist statistic"
        );
    }

    private function thenPlayerShouldNotHaveNatamistStatistic(FunctionalTester $I): void
    {
        $closedDaedalusId = $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId();
        $I->assertNull(
            actual: $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
                StatisticEnum::NATAMIST,
                $this->player->getUser()->getId(),
                $closedDaedalusId
            ),
            message: "{$this->player->getLogName()} should have NOT natamist statistic"
        );
    }

    private function thenPlayerShouldHaveLastManStandingStatistic(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: 1,
            actual: $this->statisticRepository->findByNameAndUserIdOrNull(
                StatisticEnum::LAST_MEMBER,
                $this->player->getUser()->getId()
            )?->getCount(),
        );
    }

    private function thenLastMandStandingStatisticShouldBeAttributedOnce(FunctionalTester $I): void
    {
        $lastManStat = 0;

        foreach ($this->daedalus->getPlayers() as $player) {
            $lastManStat += $this->statisticRepository->findByNameAndUserIdOrNull(
                name: StatisticEnum::LAST_MEMBER,
                userId: $player->getUser()->getId(),
            )?->getCount() ?? 0;
        }

        $I->assertEquals(1, $lastManStat);
    }

    private function thenLastMandStandingStatisticShouldNotBeAttributed(FunctionalTester $I): void
    {
        $closedDaedalusId = $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId();

        foreach ($this->daedalus->getPlayers() as $player) {
            $I->assertNull(
                $this->statisticRepository->findByNameAndUserIdOrNull(
                    name: StatisticEnum::LAST_MEMBER,
                    userId: $player->getUser()->getId(),
                )
            );

            $I->assertNull(
                $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
                    name: StatisticEnum::LAST_MEMBER,
                    userId: $player->getUser()->getId(),
                    closedDaedalusId: $closedDaedalusId,
                )
            );
        }
    }

    private function thenPlayerShouldHaveLastCommandantStatistic(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: 1,
            actual: $this->statisticRepository->findByNameAndUserIdOrNull(
                StatisticEnum::COMMANDER_SHOULD_GO_LAST,
                $this->player->getUser()->getId()
            )?->getCount(),
        );
    }

    private function thenPlayerShouldNotHaveLastCommandantStatistic(FunctionalTester $I): void
    {
        $I->assertNull(
            actual: $this->statisticRepository->findByNameAndUserIdOrNull(
                StatisticEnum::COMMANDER_SHOULD_GO_LAST,
                $this->player->getUser()->getId()
            ),
        );
    }
}
