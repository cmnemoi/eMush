<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Skill\Service;

use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Repository\InMemoryPlayerRepository;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DeletePlayerSkillServiceTest extends TestCase
{
    private DeletePlayerSkillService $deletePlayerSkillService;
    private Player $player;
    private InMemoryPlayerRepository $playerRepository;
    private InMemorySkillRepository $skillRepository;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->playerRepository = new InMemoryPlayerRepository();
        $this->skillRepository = new InMemorySkillRepository();
        $this->player = PlayerFactory::createPlayer();

        $this->deletePlayerSkillService = new DeletePlayerSkillService($this->skillRepository);
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        $this->player = PlayerFactory::createNullPlayer();
        $this->playerRepository->clear();
        $this->skillRepository->clear();
    }

    public function testShouldDeletePlayerSkill(): void
    {
        $this->givenPlayerHasSkill(SkillEnum::APPRENTICE);

        $this->whenIDeleteSkill(SkillEnum::APPRENTICE);

        $this->thenPlayerShouldNotHaveSkill(SkillEnum::APPRENTICE);
    }

    public function testShouldThrowIfPlayerDoesNotHaveSkill(): void
    {
        $this->expectException(\Exception::class);

        $this->whenIDeleteSkill(SkillEnum::NULL);
    }

    private function givenPlayerHasSkill(SkillEnum $skill): void
    {
        $skill = Skill::createByNameForPlayer($skill, $this->player);
        $this->playerRepository->save($this->player);
    }

    private function whenIDeleteSkill(SkillEnum $skill): void
    {
        $this->deletePlayerSkillService->execute(skill: $skill, player: $this->player);
    }

    private function thenPlayerShouldNotHaveSkill(SkillEnum $skill): void
    {
        $player = $this->playerRepository->findOneByName($this->player->getName());
        self::assertFalse($player?->hasSkill($skill));
    }
}

final class DeletePlayerSkillService
{
    public function __construct(private SkillRepositoryInterface $skillRepository) {}

    public function execute(SkillEnum $skill, Player $player): void
    {
        $this->skillRepository->delete($player->getSkillByNameOrThrow($skill));
    }
}

interface SkillRepositoryInterface
{
    public function delete(Skill $skill): void;
}

final class InMemorySkillRepository implements SkillRepositoryInterface
{
    private array $skills = [];

    public function delete(Skill $skill): void
    {
        $player = $skill->getPlayer();
        $player->removeSkill($skill);

        unset($this->skills[$skill->getName()->toString()]);
    }

    public function save(Skill $skill): void
    {
        $this->skills[$skill->getName()->toString()] = $skill;
    }

    public function clear(): void
    {
        $this->skills = [];
    }
}
