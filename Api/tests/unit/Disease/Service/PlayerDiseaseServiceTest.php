<?php

namespace Mush\Tests\unit\Disease\Service;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Disease\ConfigData\DiseaseConfigData;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Disease\Enum\InjuryEnum;
use Mush\Disease\Event\DiseaseEvent;
use Mush\Disease\Repository\InMemoryPlayerDiseaseRepository;
use Mush\Disease\Service\PlayerDiseaseService;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\FakeD100RollService as D100Roll;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\ConfigData\ModifierConfigData;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PlayerDiseaseServiceTest extends TestCase
{
    private PlayerDiseaseService $playerDiseaseService;

    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;

    /** @var Mockery\Mock|RandomServiceInterface */
    private RandomServiceInterface $randomService;

    private InMemoryPlayerDiseaseRepository $playerDiseaseRepository;

    /**
     * @before
     */
    public function before()
    {
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->playerDiseaseRepository = new InMemoryPlayerDiseaseRepository();

        $this->playerDiseaseService = new PlayerDiseaseService(
            d100Roll: new D100Roll(isSuccessful: true),
            eventService: $this->eventService,
            randomService: $this->randomService,
            playerDiseaseRepository: $this->playerDiseaseRepository,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
        $this->playerDiseaseRepository->clear();
    }

    public function testCreateDiseaseFromNameAndWithDiseaseConfigDelay()
    {
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDelayMin(4)->setDelayLength(4)
            ->setDiseaseName('name');

        $gameConfig = new GameConfig();
        $gameConfig->addDiseaseConfig($diseaseConfig);

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $player = new Player();
        $player->setDaedalus($daedalus);

        $this
            ->randomService
            ->shouldReceive('random')
            ->withArgs([$diseaseConfig->getDelayMin(), $diseaseConfig->getDelayMin() + $diseaseConfig->getDelayLength()])
            ->andReturn(4)
            ->once();
        $this->eventService->shouldReceive('callEvent')->once();

        $disease = $this->playerDiseaseService->createDiseaseFromName('name', $player, [DiseaseCauseEnum::INCUBATING_END]);

        $savedDisease = $this->playerDiseaseRepository->findByIdOrThrow($disease->getId());

        self::assertSame($diseaseConfig, $savedDisease->getDiseaseConfig());
        self::assertSame($player, $savedDisease->getPlayer());
        self::assertSame(4, $savedDisease->getDiseasePoint());
        self::assertSame(DiseaseStatusEnum::INCUBATING, $savedDisease->getStatus());
    }

    public function testCreateDiseaseFromNameAndWithArgumentsDelay()
    {
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setDiseaseName('name');

        $gameConfig = new GameConfig();
        $gameConfig->addDiseaseConfig($diseaseConfig);
        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());
        $player = new Player();
        $player->setDaedalus($daedalus);

        $this->randomService
            ->shouldReceive('random')
            ->withArgs([10, 15])
            ->andReturn(4)
            ->once();
        $this->eventService->shouldReceive('callEvent')->once();

        $disease = $this->playerDiseaseService->createDiseaseFromName('name', $player, ['cause'], 10, 5);

        $savedDisease = $this->playerDiseaseRepository->findByIdOrThrow($disease->getId());

        self::assertInstanceOf(PlayerDisease::class, $savedDisease);
        self::assertSame($diseaseConfig, $savedDisease->getDiseaseConfig());
        self::assertSame($player, $savedDisease->getPlayer());
        self::assertSame(4, $savedDisease->getDiseasePoint());
        self::assertSame(DiseaseStatusEnum::INCUBATING, $savedDisease->getStatus());
    }

    public function testCreateDiseaseFromNameAndWithoutDelay()
    {
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setDiseaseName('name');

        $gameConfig = new GameConfig();
        $gameConfig->addDiseaseConfig($diseaseConfig);

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());
        $player = new Player();
        $player->setDaedalus($daedalus);

        $this->randomService
            ->shouldReceive('random')
            ->withArgs([$diseaseConfig->getDiseasePointMin(), $diseaseConfig->getDiseasePointMin() + $diseaseConfig->getDiseasePointLength()])
            ->andReturn(4)
            ->once();
        $this->eventService->shouldReceive('callEvent')->twice();

        $disease = $this->playerDiseaseService->createDiseaseFromName('name', $player, ['reason']);

        $savedDisease = $this->playerDiseaseRepository->findByIdOrThrow($disease->getId());

        self::assertInstanceOf(PlayerDisease::class, $savedDisease);
        self::assertSame($diseaseConfig, $savedDisease->getDiseaseConfig());
        self::assertSame($player, $savedDisease->getPlayer());
        self::assertSame(4, $savedDisease->getDiseasePoint());
        self::assertSame(DiseaseStatusEnum::ACTIVE, $savedDisease->getStatus());
    }

    public function testHandleNewCycleIncubatedDiseaseAppear()
    {
        $daedalus = new Daedalus();
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseaseConfig = DiseaseConfig::fromConfigData(DiseaseConfigData::getByName(DiseaseEnum::ACID_REFLUX));
        $diseasePlayer = new PlayerDisease();
        $diseasePlayer
            ->setPlayer($player)
            ->setStatus(DiseaseStatusEnum::INCUBATING)
            ->setDiseaseConfig($diseaseConfig)
            ->setDiseasePoint(1);
        $this->eventService->shouldReceive('callEvent')->once();

        $this->randomService->shouldReceive('random')->andReturn(10);

        $this->playerDiseaseService->handleNewCycle($diseasePlayer, new \DateTime());

        $savedDisease = $this->playerDiseaseRepository->findByIdOrThrow($diseasePlayer->getId());
        self::assertSame(10, $savedDisease->getDiseasePoint());
        self::assertSame(DiseaseStatusEnum::ACTIVE, $savedDisease->getStatus());
    }

    public function testHandleNewCycleIncubatedDiseaseAppearAndOverrodeDisease()
    {
        $daedalus = new Daedalus();
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseaseConfig = DiseaseConfig::fromConfigData(DiseaseConfigData::getByName(InjuryEnum::BROKEN_SHOULDER));
        $diseaseConfig->setOverride([InjuryEnum::BROKEN_SHOULDER]);
        $diseasePlayer = new PlayerDisease();
        $diseasePlayer
            ->setPlayer($player)
            ->setStatus(DiseaseStatusEnum::INCUBATING)
            ->setDiseaseConfig($diseaseConfig)
            ->setDiseasePoint(1);

        $diseaseConfig2 = DiseaseConfig::fromConfigData(DiseaseConfigData::getByName(InjuryEnum::BROKEN_SHOULDER));
        $diseasePlayer2 = new PlayerDisease();
        $diseasePlayer2
            ->setPlayer($player)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseaseConfig($diseaseConfig2)
            ->setDiseasePoint(1);
        $player->addMedicalCondition($diseasePlayer2);

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(
                static fn (DiseaseEvent $event) => (
                    $event->getPlayerDisease() === $diseasePlayer
                )
                    && \in_array(DiseaseCauseEnum::INCUBATING_END, $event->getTags(), true)
            )
            ->once();

        $this->randomService->shouldReceive('random')->andReturn(10);

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(
                static fn (DiseaseEvent $event) => (
                    $event->getPlayerDisease() === $diseasePlayer2
                )
                    && \in_array(DiseaseCauseEnum::OVERRODE, $event->getTags(), true)
            )->once();

        $this->playerDiseaseService->handleNewCycle($diseasePlayer, new \DateTime());

        $savedDisease = $this->playerDiseaseRepository->findByIdOrThrow($diseasePlayer->getId());
        self::assertSame(10, $savedDisease->getDiseasePoint());
        self::assertSame(DiseaseStatusEnum::ACTIVE, $savedDisease->getStatus());
    }

    public function testHealDisease()
    {
        $daedalus = new Daedalus();
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseasePlayer = new PlayerDisease();
        $diseasePlayer
            ->setPlayer($player)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setResistancePoint(0);

        $this->eventService->shouldReceive('callEvent')->once();

        $this->playerDiseaseService->healDisease($player, $diseasePlayer, ['reason'], new \DateTime(), VisibilityEnum::PUBLIC);

        $savedDisease = $this->playerDiseaseRepository->findByIdOrNull($diseasePlayer->getId());
        self::assertNull($savedDisease);
    }

    public function testTreatDisease()
    {
        $daedalus = new Daedalus();
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseasePlayer = new PlayerDisease();
        $diseasePlayer
            ->setPlayer($player)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setResistancePoint(1);

        $this->eventService->shouldReceive('callEvent')->once();

        $this->playerDiseaseService->healDisease($player, $diseasePlayer, ['reason'], new \DateTime(), VisibilityEnum::PUBLIC);

        $savedDisease = $this->playerDiseaseRepository->findByIdOrThrow($diseasePlayer->getId());
        self::assertSame(0, $savedDisease->getResistancePoint());
    }

    public function testHygienistShouldPreventPhysicialDiseaseCreation(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();

        $player = $this->givenPlayerWithHygienistSkill($daedalus);

        $this->whenDiseaseIsCreatedForPlayer(DiseaseEnum::ACID_REFLUX, $player);

        $this->thenPlayerShouldNotHaveDisease($player, DiseaseEnum::ACID_REFLUX);
    }

    public function testHygienistShouldNotPreventDisorderCreation(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();

        $player = $this->givenPlayerWithHygienistSkill($daedalus);

        $this->whenDiseaseIsCreatedForPlayer(DisorderEnum::AGORAPHOBIA, $player);

        $this->thenPlayerShouldHaveDisease($player, DisorderEnum::AGORAPHOBIA);
    }

    private function givenPlayerWithHygienistSkill(Daedalus $daedalus): Player
    {
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);
        Skill::createByNameForPlayer(SkillEnum::HYGIENIST, $player);
        new GameModifier(
            holder: $player,
            modifierConfig: VariableEventModifierConfig::fromConfigData(
                ModifierConfigData::getByName(ModifierNameEnum::PLAYER_50_PERCENT_CHANCE_TO_PREVENT_DISEASE)
            )
        );

        return $player;
    }

    private function whenDiseaseIsCreatedForPlayer(string $diseaseName, Player $player): void
    {
        $this->eventService->shouldIgnoreMissing();
        $this->randomService->shouldIgnoreMissing();

        $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: $diseaseName,
            player: $player,
            reasons: [],
        );
    }

    private function thenPlayerShouldNotHaveDisease(Player $player, string $diseaseName): void
    {
        self::assertNull($player->getMedicalConditionByName($diseaseName));
    }

    private function thenPlayerShouldHaveDisease(Player $player, string $diseaseName): void
    {
        self::assertNotNull($player->getMedicalConditionByName($diseaseName));
    }
}
