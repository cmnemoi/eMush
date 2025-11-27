<?php

declare(strict_types=1);

namespace Mush\test\unit\Player\Normalizer;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Normalizer\SpaceBattlePatrolShipNormalizer;
use Mush\Equipment\Normalizer\SpaceBattleTurretNormalizer;
use Mush\Equipment\Normalizer\TerminalNormalizer;
use Mush\Equipment\Repository\GameEquipmentRepositoryInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Exploration\Service\ClosedExplorationServiceInterface;
use Mush\Exploration\Service\ExplorationServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Hunter\Service\HunterNormalizerHelperInterface;
use Mush\Modifier\Service\ModifierCreationService;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Normalizer\CurrentPlayerNormalizer;
use Mush\Player\Repository\PlayerRepositoryInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\Service\PlayerVariableServiceInterface;
use Mush\Skill\ConfigData\SkillConfigData;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Repository\InMemorySkillConfigRepository;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Status\Service\FakeStatusService;
use Mush\Triumph\Repository\TriumphConfigRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class CurrentPlayerNormalizerTest extends TestCase
{
    private CurrentPlayerNormalizer $normalizer;
    private AddSkillToPlayerService $addSkillToPlayer;
    private Player $player;
    private array $normalizedPlayer;

    protected function setUp(): void
    {
        $this->normalizer = new CurrentPlayerNormalizer(
            self::createStub(GameEquipmentServiceInterface::class),
            self::createStub(PlayerServiceInterface::class),
            self::createStub(PlayerVariableServiceInterface::class),
            self::createStub(SpaceBattlePatrolShipNormalizer::class),
            self::createStub(SpaceBattleTurretNormalizer::class),
            self::createStub(TerminalNormalizer::class),
            self::createStub(TranslationServiceInterface::class),
            self::createStub(GearToolServiceInterface::class),
            self::createStub(HunterNormalizerHelperInterface::class),
            self::createStub(ClosedExplorationServiceInterface::class),
            self::createStub(ExplorationServiceInterface::class),
            self::createStub(TriumphConfigRepositoryInterface::class),
            self::createStub(GameEquipmentRepositoryInterface::class)
        );
        $this->normalizer->setNormalizer(self::createStub(NormalizerInterface::class));

        $skillConfigRepository = new InMemorySkillConfigRepository();
        $skillConfigRepository->save(SkillConfig::createFromDto(SkillConfigData::getByName(SkillEnum::FERTILE)));

        $this->addSkillToPlayer = new AddSkillToPlayerService(
            eventService: self::createStub(EventServiceInterface::class),
            gameEquipmentService: self::createStub(GameEquipmentServiceInterface::class),
            modifierCreationService: self::createStub(ModifierCreationService::class),
            playerRepository: self::createStub(PlayerRepositoryInterface::class),
            skillConfigRepository: $skillConfigRepository,
            statusService: new FakeStatusService(),
        );

        $this->player = PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus());
    }

    public function testShouldNotNormalizeFertileBonusAsSkillPoints(): void
    {
        $this->givenPlayerHasFertileSkill();

        $this->whenPlayerIsNormalized();

        $this->thenNormalizedSkillPointsShouldBeEmpty();
    }

    private function givenPlayerHasFertileSkill(): void
    {
        $this->addSkillToPlayer->execute(SkillEnum::FERTILE, $this->player);
    }

    private function whenPlayerIsNormalized(): void
    {
        $this->normalizedPlayer = $this->normalizer->normalize(
            $this->player,
            format: null,
            context: ['currentPlayer' => $this->player]
        );
    }

    private function thenNormalizedSkillPointsShouldBeEmpty(): void
    {
        self::assertEmpty($this->normalizedPlayer['skillPoints'], 'Fertile skill points should not be normalized');
    }
}
