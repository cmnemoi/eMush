<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Triumph\Event;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Repository\InMemoryGameEquipmentRepository;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Factory\PlayerFactory;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Event\ProjectEvent;
use Mush\Project\Factory\ProjectFactory;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\unit\Triumph\TestDoubles\Repository\InMemoryTriumphConfigRepository;
use Mush\Triumph\ConfigData\TriumphConfigData;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Service\ChangeTriumphFromEventService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ProjectFinishedEventTest extends TestCase
{
    private ChangeTriumphFromEventService $changeTriumphFromEventService;
    private InMemoryTriumphConfigRepository $triumphConfigRepository;
    private StatusServiceInterface $statusService;
    private InMemoryGameEquipmentRepository $gameEquipmentRepository;
    private EventServiceInterface $eventService;
    private CycleServiceInterface $cycleService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->givenStatusService();
        $this->givenEventService();
        $this->givenCycleService();
        $this->givenInMemoryGameEquipmentRepository();
        $this->givenInMemoryTriumphConfigRepository();
        $this->givenChangeTriumphFromEventService();
    }

    /**
     * @dataProvider provideShouldGiveResearchSmallTriumphToAllHumansCases
     */
    public function testShouldGiveResearchSmallTriumphToAllHumans(ProjectName $projectName): void
    {
        $daedalus = $this->givenDaedalus();
        $player = $this->givenPlayerWithDaedalus($daedalus);
        $player2 = $this->givenPlayerWithDaedalus($daedalus);
        $this->givenResearchSmallTriumphConfig();
        $event = $this->givenProjectFinishedEvent($projectName, $daedalus);
        $this->whenChangeTriumphFromEventIsExecutedFor($event);
        $this->thenPlayersShouldHaveTriumph([$player, $player2], 3);
    }

    public static function provideShouldGiveResearchSmallTriumphToAllHumansCases(): iterable
    {
        return [
            ProjectName::ANTISPORE_GAS->toString() => [ProjectName::ANTISPORE_GAS],
            ProjectName::CONSTIPASPORE_SERUM->toString() => [ProjectName::CONSTIPASPORE_SERUM],
            ProjectName::CREATE_MYCOSCAN->toString() => [ProjectName::CREATE_MYCOSCAN],
            ProjectName::MERIDON_SCRAMBLER->toString() => [ProjectName::MERIDON_SCRAMBLER],
            ProjectName::MUSHICIDE_SOAP->toString() => [ProjectName::MUSHICIDE_SOAP],
            ProjectName::MUSHOVORE_BACTERIA->toString() => [ProjectName::MUSHOVORE_BACTERIA],
            ProjectName::MYCOALARM->toString() => [ProjectName::MYCOALARM],
            ProjectName::PATULINE_SCRAMBLER->toString() => [ProjectName::PATULINE_SCRAMBLER],
            ProjectName::PHEROMODEM->toString() => [ProjectName::PHEROMODEM],
        ];
    }

    public function testShouldNotGiveResearchSmallTriumphToMush(): void
    {
        $daedalus = $this->givenDaedalus();
        $player = $this->givenPlayerWithDaedalus($daedalus);
        $this->givenPlayerIsMush($player);
        $this->givenResearchSmallTriumphConfig();
        $event = $this->givenProjectFinishedEvent(ProjectName::ANTISPORE_GAS, $daedalus);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        $this->thenPlayerShouldHaveTriumph($player, 0);
    }

    public function testShouldNotGiveResearchSmallTriumphIfResearchIsNotOnTheList(): void
    {
        $daedalus = $this->givenDaedalus();
        $player = $this->givenPlayerWithDaedalus($daedalus);
        $this->givenResearchSmallTriumphConfig();
        $event = $this->givenProjectFinishedEvent(ProjectName::MUSH_HUNTER_ZC16H, $daedalus);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        $this->thenPlayerShouldHaveTriumph($player, 0);
    }

    /**
     * @dataProvider provideShouldGiveResearchTriumphToAllHumansCases
     */
    public function testShouldGiveResearchTriumphToAllHumans(ProjectName $projectName): void
    {
        $daedalus = $this->givenDaedalus();
        $player = $this->givenPlayerWithDaedalus($daedalus);
        $player2 = $this->givenPlayerWithDaedalus($daedalus);
        $this->givenResearchTriumphConfig();
        $event = $this->givenProjectFinishedEvent($projectName, $daedalus);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        $this->thenPlayersShouldHaveTriumph([$player, $player2], 6);
    }

    public static function provideShouldGiveResearchTriumphToAllHumansCases(): iterable
    {
        return [
            ProjectName::MUSH_LANGUAGE->toString() => [ProjectName::MUSH_LANGUAGE],
            ProjectName::MUSH_HUNTER_ZC16H->toString() => [ProjectName::MUSH_HUNTER_ZC16H],
            ProjectName::MUSH_RACES->toString() => [ProjectName::MUSH_RACES],
            ProjectName::MUSH_REPRODUCTIVE_SYSTEM->toString() => [ProjectName::MUSH_REPRODUCTIVE_SYSTEM],
        ];
    }

    public function testShouldNotGiveResearchTriumphToMush(): void
    {
        $daedalus = $this->givenDaedalus();
        $player = $this->givenPlayerWithDaedalus($daedalus);
        $this->givenPlayerIsMush($player);
        $this->givenResearchTriumphConfig();
        $event = $this->givenProjectFinishedEvent(ProjectName::MUSH_HUNTER_ZC16H, $daedalus);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        $this->thenPlayerShouldHaveTriumph($player, 0);
    }

    public function testShouldNotGiveResearchTriumphIfResearchIsNotOnTheList(): void
    {
        $daedalus = $this->givenDaedalus();
        $player = $this->givenPlayerWithDaedalus($daedalus);
        $this->givenResearchTriumphConfig();
        $event = $this->givenProjectFinishedEvent(ProjectName::MUSHOVORE_BACTERIA, $daedalus);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        $this->thenPlayerShouldHaveTriumph($player, 0);
    }

    public function testShouldGiveResearchBrillantTriumphToAllHumans(): void
    {
        $daedalus = $this->givenDaedalus();
        $player = $this->givenPlayerWithDaedalus($daedalus);
        $player2 = $this->givenPlayerWithDaedalus($daedalus);
        $this->givenResearchBrillantTriumphConfig();
        $event = $this->givenProjectFinishedEvent(ProjectName::RETRO_FUNGAL_SERUM, $daedalus);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        $this->thenPlayersShouldHaveTriumph([$player, $player2], 16);
    }

    public function testShouldNotGiveResearchBrillantTriumphToMush(): void
    {
        $daedalus = $this->givenDaedalus();
        $player = $this->givenPlayerWithDaedalus($daedalus);
        $this->givenPlayerIsMush($player);
        $this->givenResearchBrillantTriumphConfig();
        $event = $this->givenProjectFinishedEvent(ProjectName::RETRO_FUNGAL_SERUM, $daedalus);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        $this->thenPlayerShouldHaveTriumph($player, 0);
    }

    public function testShouldNotGiveResearchBrillantTriumphIfResearchIsNotOnTheList(): void
    {
        $daedalus = $this->givenDaedalus();
        $player = $this->givenPlayerWithDaedalus($daedalus);
        $this->givenResearchBrillantTriumphConfig();
        $event = $this->givenProjectFinishedEvent(ProjectName::MUSHOVORE_BACTERIA, $daedalus);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        $this->thenPlayerShouldHaveTriumph($player, 0);
    }

    /**
     * @dataProvider provideShouldGiveMushSpecialistTriumphToFinolaCases
     */
    public function testShouldGiveMushSpecialistTriumphToFinola(ProjectName $projectName): void
    {
        $daedalus = $this->givenDaedalus();
        $finola = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::FINOLA, $daedalus);
        $this->givenMushSpecialistTriumphConfig();
        $event = $this->givenProjectFinishedEvent($projectName, $daedalus);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        self::assertEquals(3, $finola->getTriumph());
    }

    public static function provideShouldGiveMushSpecialistTriumphToFinolaCases(): iterable
    {
        return [
            ProjectName::ANTISPORE_GAS->toString() => [ProjectName::ANTISPORE_GAS],
            ProjectName::CONSTIPASPORE_SERUM->toString() => [ProjectName::CONSTIPASPORE_SERUM],
            ProjectName::CREATE_MYCOSCAN->toString() => [ProjectName::CREATE_MYCOSCAN],
            ProjectName::MERIDON_SCRAMBLER->toString() => [ProjectName::MERIDON_SCRAMBLER],
            ProjectName::MUSH_HUNTER_ZC16H->toString() => [ProjectName::MUSH_HUNTER_ZC16H],
            ProjectName::MUSH_LANGUAGE->toString() => [ProjectName::MUSH_LANGUAGE],
            ProjectName::MUSH_LANGUAGE->toString() => [ProjectName::MUSH_LANGUAGE],
            ProjectName::MUSH_RACES->toString() => [ProjectName::MUSH_RACES],
            ProjectName::MUSH_REPRODUCTIVE_SYSTEM->toString() => [ProjectName::MUSH_REPRODUCTIVE_SYSTEM],
            ProjectName::MUSHICIDE_SOAP->toString() => [ProjectName::MUSHICIDE_SOAP],
            ProjectName::MYCOALARM->toString() => [ProjectName::MYCOALARM],
            ProjectName::NATAMY_RIFLE->toString() => [ProjectName::NATAMY_RIFLE],
            ProjectName::PATULINE_SCRAMBLER->toString() => [ProjectName::PATULINE_SCRAMBLER],
            ProjectName::PHEROMODEM->toString() => [ProjectName::PHEROMODEM],
            ProjectName::RETRO_FUNGAL_SERUM->toString() => [ProjectName::RETRO_FUNGAL_SERUM],
            ProjectName::SPORE_SUCKER->toString() => [ProjectName::SPORE_SUCKER],
        ];
    }

    public function testShouldNotGiveMushSpecialistTriumphToMushFinola(): void
    {
        $daedalus = $this->givenDaedalus();
        $finola = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::FINOLA, $daedalus);
        $this->givenPlayerIsMush($finola);
        $this->givenMushSpecialistTriumphConfig();
        $event = $this->givenProjectFinishedEvent(ProjectName::MUSH_HUNTER_ZC16H, $daedalus);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        self::assertEquals(0, $finola->getTriumph());
    }

    public function testShouldNotGiveMushSpecialistTriumphToFinolaIfProjectNotOnTheList(): void
    {
        $daedalus = $this->givenDaedalus();
        $finola = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::FINOLA, $daedalus);
        $this->givenMushSpecialistTriumphConfig();
        $event = $this->givenProjectFinishedEvent(ProjectName::NCC_CONTACT_LENSES, $daedalus);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        self::assertEquals(0, $finola->getTriumph());
    }

    public function testShouldNotGiveMushSpecialistTriumphToOtherCharacter(): void
    {
        $daedalus = $this->givenDaedalus();
        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::HUA, $daedalus);
        $this->givenMushSpecialistTriumphConfig();
        $event = $this->givenProjectFinishedEvent(ProjectName::MUSH_HUNTER_ZC16H, $daedalus);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        self::assertEquals(0, $player->getTriumph());
    }

    /**
     * @dataProvider provideShouldGivePreciousBodyTriumphToChunCases
     */
    public function testShouldGivePreciousBodyTriumphToChun(ProjectName $projectName): void
    {
        $daedalus = $this->givenDaedalus();
        $chun = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::CHUN, $daedalus);
        $this->givenPreciousBodyTriumphConfig();
        $event = $this->givenProjectFinishedEvent($projectName, $daedalus);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        self::assertEquals(4, $chun->getTriumph());
    }

    public static function provideShouldGivePreciousBodyTriumphToChunCases(): iterable
    {
        return [
            ProjectName::CREATE_MYCOSCAN->toString() => [ProjectName::CREATE_MYCOSCAN],
            ProjectName::MUSH_HUNTER_ZC16H->toString() => [ProjectName::MUSH_HUNTER_ZC16H],
            ProjectName::MUSHICIDE_SOAP->toString() => [ProjectName::MUSHICIDE_SOAP],
            ProjectName::MUSHOVORE_BACTERIA->toString() => [ProjectName::MUSHOVORE_BACTERIA],
            ProjectName::RETRO_FUNGAL_SERUM->toString() => [ProjectName::RETRO_FUNGAL_SERUM],
        ];
    }

    public function testShouldNotGivePreciousBodyTriumphToOtherCharacter(): void
    {
        $daedalus = $this->givenDaedalus();
        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::HUA, $daedalus);
        $this->givenPreciousBodyTriumphConfig();
        $event = $this->givenProjectFinishedEvent(ProjectName::MUSH_HUNTER_ZC16H, $daedalus);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        self::assertEquals(0, $player->getTriumph());
    }

    public function testShouldGiveMagellanArkTriumphToKuanTi(): void
    {
        $daedalus = $this->givenDaedalus();
        $kuanTi = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::KUAN_TI, $daedalus);
        $this->givenMagellanArkTriumphConfig();
        $event = $this->givenProjectFinishedEvent(ProjectName::ARMOUR_CORRIDOR, $daedalus);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        self::assertEquals(3, $kuanTi->getTriumph());
    }

    public function testShouldNotGiveMagellanArkTriumphToOtherCharacter(): void
    {
        $daedalus = $this->givenDaedalus();
        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::HUA, $daedalus);
        $this->givenMagellanArkTriumphConfig();
        $event = $this->givenProjectFinishedEvent(ProjectName::ARMOUR_CORRIDOR, $daedalus);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        self::assertEquals(0, $player->getTriumph());
    }

    public function testShouldNotGiveMagellanArkTriumphIfNotNeronProject(): void
    {
        $daedalus = $this->givenDaedalus();
        $kuanTi = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::KUAN_TI, $daedalus);
        $this->givenMagellanArkTriumphConfig();
        $event = $this->givenProjectFinishedEvent(ProjectName::MUSH_HUNTER_ZC16H, $daedalus);

        $this->whenChangeTriumphFromEventIsExecutedFor($event);

        self::assertEquals(0, $kuanTi->getTriumph());
    }

    public function testShouldGivePerpetualHydrationTriumphToAllHumans(): void
    {
        $daedalus = $this->givenDaedalus();
        $player = $this->givenPlayerWithDaedalus($daedalus);
        $player2 = $this->givenPlayerWithDaedalus($daedalus);
        $this->givenPerpetualHydrationTriumphConfig();
        $event = $this->givenProjectFinishedEvent(ProjectName::PERPETUAL_HYDRATION, $daedalus);
        $this->whenChangeTriumphFromEventIsExecutedFor($event);
        $this->thenPlayersShouldHaveTriumph([$player, $player2], 3);
    }

    private function givenChangeTriumphFromEventService(): void
    {
        $this->changeTriumphFromEventService = new ChangeTriumphFromEventService(
            cycleService: $this->cycleService,
            eventService: $this->eventService,
            gameEquipmentRepository: $this->gameEquipmentRepository,
            statusService: $this->statusService,
            triumphConfigRepository: $this->triumphConfigRepository,
        );
    }

    private function givenStatusService(): void
    {
        $this->statusService = self::createStub(StatusServiceInterface::class);
    }

    private function givenInMemoryGameEquipmentRepository(): void
    {
        $this->gameEquipmentRepository = new InMemoryGameEquipmentRepository();
    }

    private function givenEventService(): void
    {
        $this->eventService = self::createStub(EventServiceInterface::class);
    }

    private function givenCycleService(): void
    {
        $this->cycleService = self::createStub(CycleServiceInterface::class);
    }

    private function givenInMemoryTriumphConfigRepository(): void
    {
        $this->triumphConfigRepository = new InMemoryTriumphConfigRepository();
    }

    private function givenResearchSmallTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(
                TriumphConfigData::getByName(TriumphEnum::RESEARCH_SMALL)
            )
        );
    }

    private function givenResearchTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(
                TriumphConfigData::getByName(TriumphEnum::RESEARCH_STANDARD)
            )
        );
    }

    private function givenResearchBrillantTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(
                TriumphConfigData::getByName(TriumphEnum::RESEARCH_BRILLANT)
            )
        );
    }

    private function givenPreciousBodyTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(
                TriumphConfigData::getByName(TriumphEnum::PRECIOUS_BODY)
            )
        );
    }

    private function givenMagellanArkTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(
                TriumphConfigData::getByName(TriumphEnum::MAGELLAN_ARK)
            )
        );
    }

    private function givenPerpetualHydrationTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(
                TriumphConfigData::getByName(TriumphEnum::PERPETUAL_HYDRATION)
            )
        );
    }

    private function givenDaedalus()
    {
        return DaedalusFactory::createDaedalus();
    }

    private function givenPlayerWithDaedalus($daedalus)
    {
        return PlayerFactory::createPlayerWithDaedalus($daedalus);
    }

    private function givenPlayerIsMush($player): void
    {
        StatusFactory::createStatusByNameForHolder(PlayerStatusEnum::MUSH, $player);
    }

    private function givenProjectFinishedEvent(ProjectName $projectName, $daedalus): ProjectEvent
    {
        $event = new ProjectEvent(
            project: ProjectFactory::createProjectByNameForDaedalus($projectName, $daedalus),
            author: PlayerFactory::createPlayer(),
        );
        $event->setEventName(ProjectEvent::PROJECT_FINISHED);

        return $event;
    }

    private function givenMushSpecialistTriumphConfig(): void
    {
        $this->triumphConfigRepository->save(
            TriumphConfig::fromDto(TriumphConfigData::getByName(TriumphEnum::MUSH_SPECIALIST))
        );
    }

    private function whenChangeTriumphFromEventIsExecutedFor(ProjectEvent $event): void
    {
        $this->changeTriumphFromEventService->execute($event);
    }

    private function thenPlayersShouldHaveTriumph(array $players, int $expectedTriumph): void
    {
        foreach ($players as $player) {
            self::assertEquals($expectedTriumph, $player->getTriumph());
        }
    }

    private function thenPlayerShouldHaveTriumph($player, int $expectedTriumph): void
    {
        self::assertEquals($expectedTriumph, $player->getTriumph());
    }
}
