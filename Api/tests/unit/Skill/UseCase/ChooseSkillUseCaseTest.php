<?php

declare(strict_types=1);

namespace Mush\tests\unit\Skill\UseCase;

use Mush\Game\Enum\CharacterEnum;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Repository\InMemoryPlayerRepository;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Status\Enum\SkillPointsEnum;
use Mush\Status\Service\FakeStatusService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ChooseSkillUseCaseTest extends TestCase
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
        $this->whenIChooseSkill(SkillEnum::PILOT);

        $this->thenPlayerShouldHaveSkill(SkillEnum::PILOT);
    }

    public function testShouldThrowWhenTryingToAddSkillIfNotInPlayerSkillConfigs(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->whenIChooseSkill(SkillEnum::ANONYMUSH);
    }

    public function testShouldNotAddSkillIfPlayerAlreadyHasIt(): void
    {
        $this->givenPlayerHasSkill(SkillEnum::PILOT);

        $this->whenIChooseSkill(SkillEnum::PILOT);

        $this->thenPlayerShouldOnlyHaveOneSkill(SkillEnum::PILOT);
    }

    public function testShouldCreateSkillPoints(): void
    {
        $this->whenIChooseSkill(SkillEnum::SHOOTER);

        $this->thenPlayerShouldHaveSkillPoints(SkillPointsEnum::SHOOTER_POINTS);
    }

    private function givenAPlayer(): Player
    {
        return PlayerFactory::createPlayerByName(CharacterEnum::TERRENCE);
    }

    private function givenPlayerHasSkill(SkillEnum $skill): void
    {
        Skill::createByNameForPlayer($skill, $this->player);
    }

    private function whenIChooseSkill(SkillEnum $skill): void
    {
        $useCase = new ChooseSkillUseCase(
            $this->createStub(ModifierCreationServiceInterface::class),
            $this->playerRepository,
            new FakeStatusService(),
        );
        $useCase->execute(new ChooseSkillDto($skill, $this->player));
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

    private function thenPlayerShouldHaveSkillPoints(SkillPointsEnum $skillPoints): void
    {
        $player = $this->playerRepository->findOneByName($this->player->getName());
        $skillPointsStatus = $player->getStatusByName($skillPoints->toString());

        self::assertNotNull($skillPointsStatus);
    }
}
