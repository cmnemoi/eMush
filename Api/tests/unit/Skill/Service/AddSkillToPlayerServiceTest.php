<?php

declare(strict_types=1);

namespace Mush\tests\unit\Skill\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Repository\InMemoryPlayerRepository;
use Mush\Skill\ConfigData\SkillConfigData;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Repository\SkillConfigRepositoryInterface;
use Mush\Skill\Service\AddSkillToPlayerService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class AddSkillToPlayerServiceTest extends TestCase
{
    private Player $player;
    private InMemoryPlayerRepository $playerRepository;
    private InMemorySkillConfigRepository $skillConfigRepository;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->playerRepository = new InMemoryPlayerRepository();
        $this->skillConfigRepository = new InMemorySkillConfigRepository();
        $this->skillConfigRepository->save(SkillConfig::createFromDto(SkillConfigData::getByName(SkillEnum::TECHNICIAN)));

        $this->player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::ANDIE, DaedalusFactory::createDaedalus());
        $this->playerRepository->save($this->player);
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        $this->player = PlayerFactory::createNullPlayer();
        $this->playerRepository->clear();
        $this->skillConfigRepository->clear();
    }

    public function testShouldAddSkillToPlayer(): void
    {
        $this->whenIAddSkillToPlayer(SkillEnum::TECHNICIAN);

        $this->thenPlayerShouldHaveSkill(SkillEnum::TECHNICIAN);
    }

    public function testShouldThrowIfSkillNotFound(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->whenIAddSkillToPlayer(SkillEnum::NULL);
    }

    private function whenIAddSkillToPlayer(SkillEnum $skill): void
    {
        $service = new AddSkillToPlayerService($this->playerRepository, $this->skillConfigRepository);
        $service->execute(skill: $skill, player: $this->player);
    }

    private function thenPlayerShouldHaveSkill(SkillEnum $skill): void
    {
        $player = $this->playerRepository->findOneByName(CharacterEnum::ANDIE);

        self::assertTrue($player->hasSkill($skill));
    }
}

final class InMemorySkillConfigRepository implements SkillConfigRepositoryInterface
{
    private array $skillConfigs = [];

    public function findOneByNameAndDaedalusOrThrow(SkillEnum $skill, Daedalus $daedalus): SkillConfig
    {
        return $this->skillConfigs[$skill->toString()] ?? throw new \InvalidArgumentException("Skill {$skill->toString()} not found for daedalus {$daedalus->getName()}");
    }

    public function clear(): void
    {
        $this->skillConfigs = [];
    }

    public function save(SkillConfig $skillConfig): void
    {
        $this->skillConfigs[$skillConfig->getName()->toString()] = $skillConfig;
    }
}
