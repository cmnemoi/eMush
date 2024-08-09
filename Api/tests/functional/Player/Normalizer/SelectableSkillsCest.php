<?php

declare(strict_types=1);

namespace Mush\tests\functional\Player\Normalizer;

use Mush\Player\Normalizer\CurrentPlayerNormalizer;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class SelectableSkillsCest extends AbstractFunctionalTest
{
    private CurrentPlayerNormalizer $normalizer;

    private array $normalizedPlayer;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->normalizer = $I->grabService(CurrentPlayerNormalizer::class);
        $this->normalizer->setNormalizer($I->grabService(NormalizerInterface::class));
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function humanShouldSeeAvailableHumanSkill(FunctionalTester $I): void
    {
        $this->givenPlayerHasHumanSkillAvailable(SkillEnum::TECHNICIAN, $I);

        $this->whenINormalizePlayer();

        $this->thenPlayerShouldSeeAvailableHumanSkill(SkillEnum::TECHNICIAN, $I);
    }

    public function humanShouldNotSeeAvailableMushSkill(FunctionalTester $I): void
    {
        $this->givenPlayerHasMushSkillAvailable(SkillEnum::ANONYMUSH, $I);

        $this->whenINormalizePlayer();

        $this->thenPlayerShouldNotSeeAvailableMushSkill($I);
    }

    public function mushShouldSeeAvailableHumanSkill(FunctionalTester $I): void
    {
        $this->givenPlayerIsMush();

        $this->givenPlayerHasHumanSkillAvailable(SkillEnum::TECHNICIAN, $I);

        $this->whenINormalizePlayer();

        $this->thenPlayerShouldSeeAvailableHumanSkill(SkillEnum::TECHNICIAN, $I);
    }

    public function mushShouldSeeAvailableMushSkill(FunctionalTester $I): void
    {
        $this->givenPlayerIsMush();

        $this->givenPlayerHasMushSkillAvailable(SkillEnum::ANONYMUSH, $I);

        $this->whenINormalizePlayer();

        $this->thenPlayerShouldSeeAvailableMushSkill(SkillEnum::ANONYMUSH, $I);
    }

    private function givenPlayerHasHumanSkillAvailable(SkillEnum $skill, FunctionalTester $I): void
    {
        $this->player->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => $skill]),
        ]);
    }

    private function givenPlayerHasMushSkillAvailable(SkillEnum $skill, FunctionalTester $I): void
    {
        $I->assertTrue($this->player->getDaedalus()->getMushSkillConfigs()->contains($I->grabEntityFromRepository(SkillConfig::class, ['name' => $skill])));
    }

    private function givenPlayerIsMush(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function whenINormalizePlayer(): void
    {
        $this->normalizedPlayer = $this->normalizer->normalize($this->player, format: null, context: ['currentPlayer' => $this->player]);
    }

    private function thenPlayerShouldSeeAvailableHumanSkill(SkillEnum $skill, FunctionalTester $I): void
    {
        $normalizedSkills = $this->normalizedPlayer['character']['selectableHumanSkills'];

        $I->assertEquals(
            expected: $skill->toString(),
            actual: $normalizedSkills[0]['key'],
        );
    }

    private function thenPlayerShouldNotSeeAvailableMushSkill(FunctionalTester $I): void
    {
        $normalizedSkills = $this->normalizedPlayer['character']['selectableMushSkills'];

        $I->assertEmpty($normalizedSkills);
    }

    private function thenPlayerShouldSeeAvailableMushSkill(SkillEnum $skill, FunctionalTester $I): void
    {
        $normalizedSkills = $this->normalizedPlayer['character']['selectableMushSkills'];

        $I->assertEquals(
            expected: $skill->toString(),
            actual: $normalizedSkills[0]['key'],
        );
    }
}
