<?php

declare(strict_types=1);

namespace Mush\tests\functional\Player\Normalizer;

use Mush\Player\Normalizer\CurrentPlayerNormalizer;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class SkillsCest extends AbstractFunctionalTest
{
    private CurrentPlayerNormalizer $normalizer;
    private array $normalizedPlayer;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->normalizer = $I->grabService(CurrentPlayerNormalizer::class);
        $this->normalizer->setNormalizer($I->grabService(NormalizerInterface::class));
    }

    public function shouldBeVisibleByHumanPlayer(FunctionalTester $I): void
    {
        $this->givenPlayerHasSkill(SkillEnum::TECHNICIAN, $I);

        $this->whenINormalizePlayer();

        $this->thenPlayerShouldSeeSkill(SkillEnum::TECHNICIAN, $I);
    }

    public function shouldNormalizeMushThenHumanSkills(FunctionalTester $I): void
    {
        $this->givenPlayerHasSkill(SkillEnum::TECHNICIAN, $I);
        $this->givenPlayerHasSkill(SkillEnum::TRANSFER, $I);

        $this->whenINormalizePlayer();

        $I->assertEquals(
            expected: [SkillEnum::TRANSFER->toString(), SkillEnum::TECHNICIAN->toString()],
            actual: array_map(static fn ($skill) => $skill['key'], $this->normalizedPlayer['skills']),
        );
    }

    public function shouldNormalizeSkillsByCreationDate(FunctionalTester $I): void
    {
        $this->givenPlayerHasSkill(SkillEnum::SHOOTER, $I);
        $this->givenPlayerHasSkill(SkillEnum::TECHNICIAN, $I);
        $this->givenPlayerHasSkill(SkillEnum::PILOT, $I);

        $this->givenSkillsAreCreatedInThisOrder([
            SkillEnum::PILOT,
            SkillEnum::SHOOTER,
            SkillEnum::TECHNICIAN,
        ]);

        $this->whenINormalizePlayer();

        $I->assertEquals(
            expected: [
                SkillEnum::PILOT->toString(),
                SkillEnum::SHOOTER->toString(),
                SkillEnum::TECHNICIAN->toString(),
            ],
            actual: array_map(static fn ($skill) => $skill['key'], $this->normalizedPlayer['skills']),
            message: 'Skills should be ordered by creation date, oldest first'
        );
    }

    private function givenPlayerHasSkill(SkillEnum $skill, FunctionalTester $I): void
    {
        $this->addSkillToPlayer($skill, $I);
    }

    private function whenINormalizePlayer(): void
    {
        $this->normalizedPlayer = $this->normalizer->normalize($this->player, format: null, context: ['currentPlayer' => $this->player]);
    }

    private function thenPlayerShouldSeeSkill(SkillEnum $skill, FunctionalTester $I): void
    {
        $normalizedSkills = $this->normalizedPlayer['skills'];

        $I->assertEquals(
            expected: $skill->toString(),
            actual: $normalizedSkills[0]['key'],
        );
    }

    private function givenSkillsAreCreatedInThisOrder(): void
    {
        $dates = [
            SkillEnum::PILOT->toString() => '-3 day',
            SkillEnum::SHOOTER->toString() => '-2 day',
            SkillEnum::TECHNICIAN->toString() => '-1 day',
        ];

        foreach ($dates as $skillName => $date) {
            $skill = $this->player->getSkillByNameOrThrow(SkillEnum::from($skillName));
            (new \ReflectionProperty($skill, 'createdAt'))->setValue($skill, new \DateTime($date));
        }
    }
}
