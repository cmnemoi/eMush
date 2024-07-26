<?php

declare(strict_types=1);

namespace Mush\tests\unit\Skill\UseCase;

use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Repository\InMemoryPlayerRepository;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\AddSkillToPlayerUseCase;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class AddSkillToPlayerUseCaseTest extends TestCase
{
    private Player $player;
    private InMemoryPlayerRepository $playerRepository;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->player = $this->givenAPlayer();
        $this->playerRepository = new InMemoryPlayerRepository();
        $this->playerRepository->save($this->player);
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        $this->playerRepository->clear();
    }

    public function testShouldAddSkillToPlayer(): void
    {
        $this->whenIAddSkillToPlayer(SkillEnum::PILOT);

        $this->thenPlayerShouldHaveSkill(SkillEnum::PILOT);
    }

    public function testShouldThrowWhenTryingToAddSkillIfNotInPlayerSkillConfigs(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->whenIAddSkillToPlayer(SkillEnum::ANONYMUSH);
    }

    public function testShouldNotAddSkillIfPlayerAlreadyHasIt(): void
    {
        $this->givenPlayerHasSkill(SkillEnum::PILOT);

        $this->whenIAddSkillToPlayer(SkillEnum::PILOT);

        $this->thenPlayerShouldOnlyHaveOneSkill(SkillEnum::PILOT);
    }

    private function givenAPlayer(): Player
    {
        return PlayerFactory::createPlayerByName(CharacterEnum::ANDIE);
    }

    private function givenPlayerHasSkill(SkillEnum $skill): void
    {
        new Skill(new SkillConfig($skill), $this->player);
    }

    private function whenIAddSkillToPlayer(SkillEnum $skill): void
    {
        $useCase = new AddSkillToPlayerUseCase(
            $this->createStub(EventServiceInterface::class),
            $this->playerRepository,
        );
        $useCase->execute($skill, $this->player);
    }

    private function thenPlayerShouldHaveSkill(SkillEnum $skill): void
    {
        $player = $this->playerRepository->findOneByName($this->player->getName());
        $addedSkill = $player->getSkillByNameOrThrow($skill);
        self::assertEquals($skill, $addedSkill->getName());
    }

    private function thenPlayerShouldOnlyHaveOneSkill(SkillEnum $skillName): void
    {
        $player = $this->playerRepository->findOneByName($this->player->getName());
        $skills = $player->getSkills();
        $skills = $skills->filter(static fn (Skill $skill) => $skill->getName() === $skillName);

        self::assertCount(1, $skills);
    }
}
