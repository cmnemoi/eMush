<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Disease\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Disease\ConfigData\DiseaseConfigData;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\ConsumableDisease;
use Mush\Disease\Entity\ConsumableDiseaseAttribute;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use Mush\Disease\Repository\InMemoryPlayerDiseaseRepository;
use Mush\Disease\Service\ConsumableDiseaseServiceInterface;
use Mush\Disease\Service\DiseaseCauseService;
use Mush\Disease\Service\PlayerDiseaseService;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\FakeD100RollService;
use Mush\Game\Service\Random\FakeGetRandomIntegerService;
use Mush\Game\Service\Random\ProbaCollectionRandomElementService;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\ConfigData\ModifierConfigData;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Tests\unit\Modifier\TestDoubles\InMemoryModifierConfigRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DiseaseCauseServiceTest extends TestCase
{
    private DiseaseCauseService $diseaseCauseService;

    private FakeD100RollService $d100Roll;
    private ProbaCollectionRandomElementService $probaCollectionRandomElement;
    private PlayerDiseaseService $playerDiseaseService;
    private InMemoryPlayerDiseaseRepository $playerDiseaseRepository;
    private InMemoryModifierConfigRepository $inMemoryModifierConfigRepository;

    /** @var ConsumableDiseaseServiceInterface|Mockery\Mock */
    private ConsumableDiseaseServiceInterface $consumableDiseaseService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->consumableDiseaseService = \Mockery::mock(ConsumableDiseaseServiceInterface::class);
        $this->d100Roll = new FakeD100RollService();
        $this->probaCollectionRandomElement = new ProbaCollectionRandomElementService(new FakeGetRandomIntegerService(result: 0));
        $this->playerDiseaseRepository = new InMemoryPlayerDiseaseRepository();
        $this->inMemoryModifierConfigRepository = new InMemoryModifierConfigRepository();

        $eventService = self::createStub(EventServiceInterface::class);
        $randomService = self::createStub(RandomServiceInterface::class);

        $this->playerDiseaseService = new PlayerDiseaseService(
            d100Roll: $this->d100Roll,
            eventService: $eventService,
            randomService: $randomService,
            playerDiseaseRepository: $this->playerDiseaseRepository,
            modifierConfigRepository: $this->inMemoryModifierConfigRepository
        );

        $this->diseaseCauseService = new DiseaseCauseService(
            consumableDiseaseService: $this->consumableDiseaseService,
            d100Roll: $this->d100Roll,
            probaCollectionRandomElement: $this->probaCollectionRandomElement,
            playerDiseaseService: $this->playerDiseaseService,
            randomService: $randomService
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        $this->playerDiseaseRepository->clear();
        \Mockery::close();
    }

    public function testSpoiledFoodHazardous()
    {
        // setup
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setDiseaseName(DiseaseEnum::FOOD_POISONING->toString());

        $diseaseCauseConfig = new DiseaseCauseConfig();
        $diseaseCauseConfig
            ->setDiseases([DiseaseEnum::FOOD_POISONING->toString() => 1])
            ->setCauseName(DiseaseCauseEnum::PERISHED_FOOD);

        $gameConfig = new GameConfig();
        $gameConfig
            ->addDiseaseConfig($diseaseConfig)
            ->addDiseaseCauseConfig($diseaseCauseConfig);

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $player = PlayerFactory::createPlayer();
        $player->setDaedalus($daedalus);

        $gameEquipment = new GameEquipment(new Place());

        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName(EquipmentStatusEnum::HAZARDOUS);
        $hazardous = new Status($gameEquipment, $statusConfig);

        // given spoiled food roll to give disease is successful
        $this->d100Roll->makeSuccessful();

        // when spoiled food is handled
        $this->diseaseCauseService->handleSpoiledFood($player, $gameEquipment);

        // then player should have food poisoning disease
        self::assertNotNull($player->getMedicalConditionByName(DiseaseEnum::FOOD_POISONING->toString())?->getId());
    }

    public function testConsumableWithDiseases()
    {
        $modifierConfig1 = DirectModifierConfig::fromDto(ModifierConfigData::getByName('direct_modifier_player_-1_max_healthPoint'));
        $modifierConfig2 = EventModifierConfig::fromDto(ModifierConfigData::getByName('vomiting_consume'));
        $modifierConfig3 = EventModifierConfig::fromDto(ModifierConfigData::getByName('vomiting_move_random_40'));

        $this->inMemoryModifierConfigRepository->save($modifierConfig1);
        $this->inMemoryModifierConfigRepository->save($modifierConfig2);
        $this->inMemoryModifierConfigRepository->save($modifierConfig3);

        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayer();
        $player->setDaedalus($daedalus);

        $gameEquipment = new GameEquipment(new Place());
        $gameEquipment->setName('someName');

        $disease = new ConsumableDiseaseAttribute();
        $disease->setDisease(DiseaseEnum::FOOD_POISONING->toString());

        $consumableDisease = new ConsumableDisease();
        $consumableDisease
            ->setDiseasesAttribute(new ArrayCollection([$disease]));

        $this->consumableDiseaseService
            ->shouldReceive('findConsumableDiseases')
            ->andReturn($consumableDisease)
            ->twice();

        $this->d100Roll->makeFail();

        $this->diseaseCauseService->handleConsumable($player, $gameEquipment);

        self::assertNull($player->getMedicalConditionByName(DiseaseEnum::FOOD_POISONING->toString())?->getId());

        $this->d100Roll->makeSuccessful();

        $this->diseaseCauseService->handleConsumable($player, $gameEquipment);

        self::assertNotNull($player->getMedicalConditionByName(DiseaseEnum::FOOD_POISONING->toString())?->getId());
    }

    public function testConsumableWithCures()
    {
        $daedalus = DaedalusFactory::createDaedalus();

        $player = PlayerFactory::createPlayer();
        $player->setDaedalus($daedalus);

        $diseaseName = DiseaseEnum::FOOD_POISONING->toString();
        $gameEquipment = new GameEquipment(new Place());
        $gameEquipment->setName($diseaseName);

        $cure = new ConsumableDiseaseAttribute();
        $cure
            ->setType(MedicalConditionTypeEnum::CURE)
            ->setDisease($diseaseName);

        $consumableDisease = new ConsumableDisease();
        $consumableDisease
            ->setDiseasesAttribute(new ArrayCollection([$cure]));

        $this->consumableDiseaseService
            ->shouldReceive('findConsumableDiseases')
            ->andReturn($consumableDisease)
            ->once();

        $this->d100Roll->makeSuccessful();

        // given player has a disease healed by the consumable
        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setPlayer($player)
            ->setDiseaseConfig(DiseaseConfig::fromDto(DiseaseConfigData::getByName(DiseaseEnum::FOOD_POISONING->toString())));
        $player->addMedicalCondition($playerDisease);
        $this->playerDiseaseRepository->save($playerDisease);

        // when consumable is handled
        $this->diseaseCauseService->handleConsumable($player, $gameEquipment);

        // then player should not have the disease
        self::assertNull($player->getMedicalConditionByName($diseaseName)?->getId(), 'Player should not have the disease');
    }

    public function testHandleDiseaseForCause()
    {
        // given food poisoning disease cause
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setDiseaseName(DiseaseEnum::FOOD_POISONING->toString());

        $diseaseCauseConfig = new DiseaseCauseConfig();
        $diseaseCauseConfig
            ->setDiseases([DiseaseEnum::FOOD_POISONING->toString() => 1])
            ->setCauseName(DiseaseCauseEnum::PERISHED_FOOD);

        $gameConfig = new GameConfig();
        $gameConfig
            ->addDiseaseConfig($diseaseConfig)
            ->addDiseaseCauseConfig($diseaseCauseConfig);

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());
        $player = PlayerFactory::createPlayer();
        $player->setDaedalus($daedalus);

        // when I create disease for perished food cause
        $this->diseaseCauseService->handleDiseaseForCause(DiseaseCauseEnum::PERISHED_FOOD, $player);

        // then player should have food poisoning disease
        self::assertNotNull($player->getMedicalConditionByName(DiseaseEnum::FOOD_POISONING->toString())?->getId());
    }
}
