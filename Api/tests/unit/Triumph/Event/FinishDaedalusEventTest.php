<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Triumph\Event;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Tests\unit\Triumph\TestDoubles\Repository\InMemoryTriumphConfigRepository;
use Mush\Triumph\ConfigData\TriumphConfigData;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Service\ChangeTriumphFromEventService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class FinishDaedalusEventTest extends TestCase
{
    private ChangeTriumphFromEventService $changeTriumphFromEventService;
    private InMemoryTriumphConfigRepository $triumphConfigRepository;
    private EventServiceInterface $eventService;

    private Daedalus $daedalus;
    private Player $player;
    private Player $player2;

    protected function setUp(): void
    {
        $this->givenATriumphConfigRepository();
        $this->givenAnEventService();
        $this->givenAChangeTriumphFromEventService();
        $this->givenADaedalusWithTwoPlayers();
    }

    public function testShouldGiveReturnToSolTriumphToAllHumanWhenReturningToSol(): void
    {
        $this->givenAReturnToSolTriumphConfig();

        $this->whenDaedalusFinishesWithReturnToSol();

        $this->thenAllPlayersHaveTriumphPoints(20);
    }

    public function testShouldNotGiveReturnToSolTriumphToAllHumanWhenNotReturningToSol(): void
    {
        $this->givenAReturnToSolTriumphConfig();

        $this->whenDaedalusFinishesWithoutReturnToSol();

        $this->thenAllPlayersHaveTriumphPoints(0);
    }

    public function testShouldGiveSolMushIntruderTriumphToAllHumansGivenNumberOfMushPlayers(): void
    {
        $this->givenASolMushIntruderTriumphConfig();

        $this->givenPlayerIsMush($this->player2);
        $player3 = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        $this->givenPlayerIsMush($player3);

        $this->player->setTriumph(50);

        $this->whenDaedalusFinishesWithReturnToSol();

        $this->thenPlayerShouldHaveTriumphPoints($this->player, 30); // 50 + (-10) * 2
    }

    public function testShouldGiveEdenAtLeastTriumphToAllHumanWhenTravelingToEden(): void
    {
        $this->givenAnEdenAtLeastTriumphConfig();

        $this->whenDaedalusFinishesWithTravelToEden();

        $this->thenAllPlayersHaveTriumphPoints(6);
    }

    public function testShouldGiveEdenMushInvasionTriumphToAllMushWhenTravelingToEden(): void
    {
        $this->givenAnEdenMushInvasionTriumphConfig();

        $this->givenPlayerIsMush($this->player);

        $this->whenDaedalusFinishesWithTravelToEden();

        $this->thenPlayerShouldHaveTriumphPoints($this->player, 32);
    }

    public function testShouldGiveEdenMushIntruderTriumphToAllHumansWhenTravelingToEden(): void
    {
        $this->givenAnEdenMushIntruderTriumphConfig();

        $this->givenPlayerIsMush($this->player2);
        $player3 = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        $this->givenPlayerIsMush($player3);

        $this->player->setTriumph(50);

        $this->whenDaedalusFinishesWithTravelToEden();

        $this->thenPlayerShouldHaveTriumphPoints($this->player, 18); // 50 + (-16) * 2
    }

    public function testShouldGiveEdenOneManTriumphToAllHumansWhenTravelingToEden(): void
    {
        $this->givenAnEdenOneManTriumphConfig();

        $player3 = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        $this->givenPlayerIsMush($player3);

        $this->whenDaedalusFinishesWithTravelToEden();

        $this->thenPlayerShouldHaveTriumphPoints($this->player, 3); // 1 * 3
        $this->thenPlayerShouldHaveTriumphPoints($this->player2, 3);
        $this->thenPlayerShouldHaveTriumphPoints($player3, 0);
    }

    public function testShouldGiveEdenEngineersToAllHumanTechniciansWhenTravelingToEden(): void
    {
        $this->givenAnEdenEngineersTriumphConfig();

        $player3 = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        $this->givenPlayerIsMush($player3);

        Skill::createByNameForPlayer(SkillEnum::TECHNICIAN, $this->player2);
        Skill::createByNameForPlayer(SkillEnum::TECHNICIAN, $player3);

        $this->whenDaedalusFinishesWithTravelToEden();

        $this->thenPlayerShouldHaveTriumphPoints($this->player, 0);
        $this->thenPlayerShouldHaveTriumphPoints($this->player2, 6);
        $this->thenPlayerShouldHaveTriumphPoints($player3, 0);
    }

    public function testShouldGiveEdenBiologistsToAllHumansWhoCanReadPillsWhenTravelingToEden(): void
    {
        $this->givenAnEdenBiologistsTriumphConfig();

        $player3 = PlayerFactory::createPlayerWithDaedalus($this->daedalus);

        Skill::createByNameForPlayer(SkillEnum::BIOLOGIST, $this->player);
        Skill::createByNameForPlayer(SkillEnum::NURSE, $this->player);
        Skill::createByNameForPlayer(SkillEnum::NURSE, $this->player2);
        Skill::createByNameForPlayer(SkillEnum::POLYVALENT, $player3);

        $this->whenDaedalusFinishesWithTravelToEden();

        $this->thenAllPlayersHaveTriumphPoints(3);
    }

    public function testShouldGiveSaviorToJinSuOnlyWhenTravelingToEden(): void
    {
        $this->givenASaviorTriumphConfig();

        $jinSu = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::JIN_SU, $this->daedalus);

        $this->whenDaedalusFinishesWithTravelToEden();

        $this->thenPlayerShouldHaveTriumphPoints($jinSu, 8);
        $this->thenPlayerShouldHaveTriumphPoints($this->player, 0);
    }

    public function testShouldGiveRemedyToChunOnlyWhenTravelingToEden(): void
    {
        $this->givenARemedyTriumphConfig();

        $chun = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::CHUN, $this->daedalus);

        $this->whenDaedalusFinishesWithTravelToEden();

        $this->thenPlayerShouldHaveTriumphPoints($chun, 4);
        $this->thenPlayerShouldHaveTriumphPoints($this->player, 0);
    }

    private function givenATriumphConfigRepository(): void
    {
        $this->triumphConfigRepository = new InMemoryTriumphConfigRepository();
    }

    private function givenAnEventService(): void
    {
        /** @var EventServiceInterface $eventService */
        $eventService = $this->createStub(EventServiceInterface::class);
        $this->eventService = $eventService;
    }

    private function givenAChangeTriumphFromEventService(): void
    {
        $this->changeTriumphFromEventService = new ChangeTriumphFromEventService(
            eventService: $this->eventService,
            triumphConfigRepository: $this->triumphConfigRepository,
        );
    }

    private function givenADaedalusWithTwoPlayers(): void
    {
        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->player = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        $this->player2 = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
    }

    private function givenAReturnToSolTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(TriumphConfigData::getByName(TriumphEnum::RETURN_TO_SOL))
        );
    }

    private function givenASolMushIntruderTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(TriumphConfigData::getByName(TriumphEnum::SOL_MUSH_INTRUDER))
        );
    }

    private function givenAnEdenAtLeastTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(TriumphConfigData::getByName(TriumphEnum::EDEN_AT_LEAST))
        );
    }

    private function givenAnEdenMushInvasionTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(TriumphConfigData::getByName(TriumphEnum::EDEN_MUSH_INVASION))
        );
    }

    private function givenAnEdenMushIntruderTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(TriumphConfigData::getByName(TriumphEnum::EDEN_MUSH_INTRUDER))
        );
    }

    private function givenAnEdenOneManTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(TriumphConfigData::getByName(TriumphEnum::EDEN_ONE_MAN))
        );
    }

    private function givenAnEdenEngineersTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(TriumphConfigData::getByName(TriumphEnum::EDEN_ENGINEERS))
        );
    }

    private function givenAnEdenBiologistsTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(TriumphConfigData::getByName(TriumphEnum::EDEN_BIOLOGISTS))
        );
    }

    private function givenASaviorTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(TriumphConfigData::getByName(TriumphEnum::SAVIOR))
        );
    }

    private function givenARemedyTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(TriumphConfigData::getByName(TriumphEnum::REMEDY))
        );
    }

    private function givenPlayerIsMush(Player $player): void
    {
        StatusFactory::createStatusByNameForHolder(
            name: PlayerStatusEnum::MUSH,
            holder: $player,
        );
    }

    private function whenDaedalusFinishesWithReturnToSol(): void
    {
        $event = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [ActionEnum::RETURN_TO_SOL->toString()],
            time: new \DateTime(),
        );
        $event->setEventName(DaedalusEvent::FINISH_DAEDALUS);

        $this->changeTriumphFromEventService->execute($event);
    }

    private function whenDaedalusFinishesWithTravelToEden(): void
    {
        $event = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [ActionEnum::TRAVEL_TO_EDEN->toString()],
            time: new \DateTime(),
        );
        $event->setEventName(DaedalusEvent::FINISH_DAEDALUS);

        $this->changeTriumphFromEventService->execute($event);
    }

    private function whenDaedalusFinishesWithoutReturnToSol(): void
    {
        $event = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
        $event->setEventName(DaedalusEvent::FINISH_DAEDALUS);

        $this->changeTriumphFromEventService->execute($event);
    }

    private function thenAllPlayersHaveTriumphPoints(int $expectedPoints): void
    {
        foreach ($this->daedalus->getAlivePlayers() as $player) {
            self::assertEquals($expectedPoints, $player->getTriumph());
        }
    }

    private function thenPlayerShouldHaveTriumphPoints(Player $player, int $expectedPoints): void
    {
        self::assertEquals($expectedPoints, $player->getTriumph());
    }
}
