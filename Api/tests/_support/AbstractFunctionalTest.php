<?php

namespace Mush\Tests;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NoResultException;
use Mush\Chat\Entity\Channel;
use Mush\Chat\Enum\ChannelScopeEnum;
use Mush\Communications\Service\CreateLinkWithSolForDaedalusService;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Daedalus\Entity\TitlePriority;
use Mush\Daedalus\ValueObject\GameDate;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Event\PlayerEvent;
use Mush\Project\ConfigData\ProjectConfigData;
use Mush\Project\Entity\Project;
use Mush\Project\Entity\ProjectConfig;
use Mush\Project\Event\ProjectEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\User\Entity\User;
use Symfony\Component\Uid\Uuid;

class AbstractFunctionalTest
{
    protected Daedalus $daedalus;
    protected PlayerCollection $players;
    protected Player $player;
    protected Player $player1;
    protected Player $player2;
    protected Player $chun;
    protected Player $kuanTi;
    protected Channel $publicChannel;
    protected Channel $mushChannel;

    protected CreateLinkWithSolForDaedalusService $createLinkWithSolForDaedalus;

    public function _before(FunctionalTester $I)
    {
        $this->createLinkWithSolForDaedalus = $I->grabService(CreateLinkWithSolForDaedalusService::class);

        $this->daedalus = $this->createDaedalus($I);
        $this->players = $this->createPlayers($I, $this->daedalus);
        $this->daedalus->setPlayers($this->players);
        $I->haveInRepository($this->daedalus);

        $this->player1 = $this->players->first();
        $this->player2 = $this->players->last();
        $this->player = $this->player1;
        $this->chun = $this->player1;
        $this->kuanTi = $this->player2;

        $this->createAllProjects($I);
        $this->createLinkWithSolForDaedalus->execute($this->daedalus->getId());
    }

    protected function createDaedalus(FunctionalTester $I): Daedalus
    {
        /** @var DaedalusConfig $daedalusConfig */
        $daedalusConfig = $I->grabEntityFromRepository(DaedalusConfig::class, ['name' => GameConfigEnum::DEFAULT]);

        /** @var Daedalus $daedalus */
        $daedalus = new Daedalus();
        $daedalus
            ->setCycle(0)
            ->setDaedalusVariables($daedalusConfig)
            ->setCycleStartedAt(new \DateTime());

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);
        $neron = new Neron();
        $I->haveInRepository($neron);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo
            ->setName(Uuid::v4()->toRfc4122())
            ->setNeron($neron);
        $I->haveInRepository($daedalusInfo);

        $this->publicChannel = new Channel();
        $this->publicChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);
        $I->haveInRepository($this->publicChannel);

        $this->mushChannel = new Channel();
        $this->mushChannel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::MUSH);
        $I->haveInRepository($this->mushChannel);

        $I->haveInRepository($daedalusInfo);

        $places = $this->createPlaces($I, $daedalus);
        $daedalus->setPlaces($places);

        $daedalus->setDaedalusVariables($daedalusConfig);

        $this->createTitlePriorities($daedalus, $I);

        $daedalus->setGameDate(new GameDate($daedalus, 1, 1));

        $I->haveInRepository($daedalus);

        return $daedalus;
    }

    protected function createPlayers(FunctionalTester $I, Daedalus $daedalus): PlayerCollection
    {
        $players = new PlayerCollection([]);
        $characterNames = [CharacterEnum::CHUN, CharacterEnum::KUAN_TI];

        foreach ($characterNames as $characterName) {
            $player = $this->addPlayerByCharacter($I, $daedalus, $characterName);
            $players->add($player);
        }

        return $players;
    }

    protected function createPlaces(FunctionalTester $I, Daedalus $daedalus): ArrayCollection
    {
        /** @var PlaceConfig $laboratoryConfig */
        $laboratoryConfig = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::LABORATORY]);
        $laboratory = new Place();
        $laboratory
            ->setName(RoomEnum::LABORATORY)
            ->setType($laboratoryConfig->getType())
            ->setDaedalus($daedalus);
        $I->haveInRepository($laboratory);

        /** @var PlaceConfig $spaceConfig */
        $spaceConfig = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::SPACE]);
        $space = new Place();
        $space
            ->setName(RoomEnum::SPACE)
            ->setType($spaceConfig->getType())
            ->setDaedalus($daedalus);
        $I->haveInRepository($space);

        /** @var PlaceConfig $planetConfig */
        $planetConfig = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::PLANET]);
        $planet = new Place();
        $planet
            ->setName(RoomEnum::PLANET)
            ->setType($planetConfig->getType())
            ->setDaedalus($daedalus);
        $I->haveInRepository($planet);
        $planetDepthsConfig = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::PLANET_DEPTHS]);
        $planetDepths = new Place();
        $planetDepths
            ->setName(RoomEnum::PLANET_DEPTHS)
            ->setType($planetDepthsConfig->getType())
            ->setDaedalus($daedalus);
        $I->haveInRepository($planetDepths);

        return new ArrayCollection([$laboratory, $space, $planet, $planetDepths]);
    }

    protected function createExtraPlace(string $placeName, FunctionalTester $I, Daedalus $daedalus): Place
    {
        /** @var PlaceConfig $extraRoomConfig */
        $extraRoomConfig = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => $placeName]);
        $extraRoom = new Place();
        $extraRoom
            ->setName($placeName)
            ->setType($extraRoomConfig->getType())
            ->setDaedalus($daedalus);
        $I->haveInRepository($extraRoom);
        $I->haveInRepository($daedalus);

        return $extraRoom;
    }

    protected function addPlayerByCharacter(FunctionalTester $I, Daedalus $daedalus, string $characterName): Player
    {
        $characterConfig = $I->grabEntityFromRepository(CharacterConfig::class, ['characterName' => $characterName]);

        $daedalus->addAvailableCharacter($characterConfig);

        $player = new Player();

        $user = new User();
        $user
            ->setUserId(Uuid::v4()->toRfc4122())
            ->setUserName(Uuid::v4()->toRfc4122());
        $I->haveInRepository($user);

        $playerInfo = new PlayerInfo($player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);

        $player->setDaedalus($daedalus);
        $player->setPlace($daedalus->getPlaceByName(RoomEnum::LABORATORY));
        $player->setPlayerVariables($characterConfig);
        $player->setAvailableHumanSkills($characterConfig->getSkillConfigs());

        $I->haveInRepository($player);

        return $player;
    }

    protected function convertPlayerToMush(FunctionalTester $I, Player $player): Player
    {
        $eventService = $I->grabService(EventServiceInterface::class);

        $conversionEvent = new PlayerEvent(
            player: $player,
            tags: [],
            time: new \DateTime(),
        );
        $eventService->callEvent($conversionEvent, PlayerEvent::CONVERSION_PLAYER);

        return $player;
    }

    protected function finishProject(Project $project, Player $author, FunctionalTester $I): void
    {
        $project->makeProgressAndUpdateParticipationDate(100);
        $I->haveInRepository($project);

        $eventService = $I->grabService(EventServiceInterface::class);
        $projectEvent = new ProjectEvent(
            project: $project,
            author: $author,
        );
        $eventService->callEvent($projectEvent, ProjectEvent::PROJECT_FINISHED);
    }

    protected function ISeeTranslatedRoomLogInRepository(string $expectedRoomLog, RoomLogDto $actualRoomLogDto, FunctionalTester $I): void
    {
        try {
            $roomLog = $I->grabEntityFromRepository(
                entity: RoomLog::class,
                params: $actualRoomLogDto->toArray(),
            );
        } catch (NoResultException $e) {
            $I->fail("Room log {$actualRoomLogDto->log} not found!");
        }

        /** @var TranslationServiceInterface $translationService */
        $translationService = $I->grabService(TranslationServiceInterface::class);

        $actualRoomLog = $translationService->translate(
            key: $roomLog->getLog(),
            parameters: $roomLog->getParameters(),
            domain: $roomLog->getType(),
            language: $actualRoomLogDto->player->getLanguage(),
        );

        $I->assertEquals(
            expected: $expectedRoomLog,
            actual: $actualRoomLog,
            message: "{$actualRoomLogDto->log} should be translated to {$expectedRoomLog}, found {$roomLog->getLog()} instead."
        );
    }

    protected function ISeeTranslatedRoomLogsInRepository(string $expectedRoomLog, RoomLogDto $actualRoomLogDto, int $number, FunctionalTester $I): void
    {
        $roomLogs = $I->grabEntitiesFromRepository(
            entity: RoomLog::class,
            params: $actualRoomLogDto->toArray(),
        );
        $logsCount = \count($roomLogs);

        $I->assertCount(
            expectedCount: $number,
            haystack: $roomLogs,
            message: "Expected {$number} RoomLogs, got {$logsCount}",
        );

        /** @var TranslationServiceInterface $translationService */
        $translationService = $I->grabService(TranslationServiceInterface::class);

        foreach ($roomLogs as $roomLog) {
            $I->assertEquals(
                expected: $expectedRoomLog,
                actual: $translationService->translate(
                    key: $roomLog->getLog(),
                    parameters: $roomLog->getParameters(),
                    domain: $roomLog->getType(),
                    language: $actualRoomLogDto->player->getLanguage(),
                ),
            );
        }
    }

    protected function addSkillToPlayer(SkillEnum $skill, FunctionalTester $I, ?Player $player = null): void
    {
        $player ??= $this->player;

        $addSkillToPlayer = $I->grabService(AddSkillToPlayerService::class);
        $addSkillToPlayer->execute($skill, $player);
    }

    protected function createAllProjects(FunctionalTester $I): void
    {
        foreach (ProjectConfigData::getAll() as $projectConfigData) {
            $projectConfig = $I->grabEntityFromRepository(ProjectConfig::class, ['name' => $projectConfigData['name']]);
            $project = new Project($projectConfig, $this->daedalus);
            $I->haveInRepository($project);

            $this->daedalus->addProject($project);
        }
    }

    private function createTitlePriorities(Daedalus $daedalus, FunctionalTester $I): void
    {
        foreach ($daedalus->getGameConfig()->getTitleConfigs() as $titleConfig) {
            $titlePriority = new TitlePriority($titleConfig, $daedalus);
            $I->haveInRepository($titlePriority);
            $daedalus->addTitlePriority($titlePriority);
        }
    }
}
