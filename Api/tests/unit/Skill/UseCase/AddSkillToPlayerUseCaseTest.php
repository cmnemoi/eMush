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
use Mush\Skill\Enum\SkillName;
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
        $this->whenIAddSkillToPlayer(SkillName::PILOT);

        $this->thenPlayerShouldHaveSkill(SkillName::PILOT);
    }

    public function testShouldThrowWhenTryingToAddSkillIfNotInPlayerSkillConfigs(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->whenIAddSkillToPlayer(SkillName::ANONYMUSH);
    }

    public function testShouldNotAddSkillIfPlayerAlreadyHasIt(): void
    {
        $this->givenPlayerHasSkill(SkillName::PILOT);

        $this->whenIAddSkillToPlayer(SkillName::PILOT);

        $this->thenPlayerShouldOnlyHaveOneSkill(SkillName::PILOT);
    }

    private function givenAPlayer(): Player
    {
        return PlayerFactory::createPlayerByName(CharacterEnum::ANDIE);
    }

    private function givenPlayerHasSkill(SkillName $skillName): void
    {
        new Skill(new SkillConfig($skillName), $this->player);
    }

    private function whenIAddSkillToPlayer(SkillName $skillName): void
    {
        $useCase = new AddSkillToPlayerUseCase(
            $this->createStub(EventServiceInterface::class),
            $this->playerRepository,
        );
        $useCase->execute($skillName, $this->player);
    }

    private function thenPlayerShouldHaveSkill(SkillName $skillName): void
    {
        $player = $this->playerRepository->findOneByName($this->player->getName());
        $addedSkill = $player->getSkillByNameOrThrow($skillName);
        self::assertEquals($skillName, $addedSkill->getName());
    }

    private function thenPlayerShouldOnlyHaveOneSkill(SkillName $skillName): void
    {
        $player = $this->playerRepository->findOneByName($this->player->getName());
        $skills = $player->getSkills();
        $skills = $skills->filter(static fn (Skill $skill) => $skill->getName() === $skillName);

        self::assertCount(1, $skills);
    }
}
