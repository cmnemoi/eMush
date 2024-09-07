<?php

declare(strict_types=1);

namespace Mush\tests\functional\Player\Normalizer;

use Mush\Player\Normalizer\CurrentPlayerNormalizer;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
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
    private AddSkillToPlayerService $addSkillToPlayer;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->normalizer = $I->grabService(CurrentPlayerNormalizer::class);
        $this->normalizer->setNormalizer($I->grabService(NormalizerInterface::class));
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->addSkillToPlayer = $I->grabService(AddSkillToPlayerService::class);
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

    public function humanShouldNotSeeAvailableSkillsIfSkillSlotsAreFilled(FunctionalTester $I): void
    {
        $this->addSkillToPlayer->execute(skill: SkillEnum::TECHNICIAN, player: $this->player);
        $this->addSkillToPlayer->execute(skill: SkillEnum::DETERMINED, player: $this->player);
        $this->addSkillToPlayer->execute(skill: SkillEnum::IT_EXPERT, player: $this->player);

        $this->whenINormalizePlayer();

        $this->thenPlayerShouldNotSeeAvailableHumanSkills($I);
    }

    public function mushShouldNotSeeAvailableSkillsIfSkillSlotsAreFilled(FunctionalTester $I): void
    {
        $this->givenPlayerIsMush();

        $this->addSkillToPlayer->execute(skill: SkillEnum::ANONYMUSH, player: $this->player);
        $this->addSkillToPlayer->execute(skill: SkillEnum::SPLASHPROOF, player: $this->player);
        $this->addSkillToPlayer->execute(skill: SkillEnum::TRANSFER, player: $this->player);
        $this->addSkillToPlayer->execute(skill: SkillEnum::INFECTOR, player: $this->player);

        $this->whenINormalizePlayer();

        $this->thenPlayerShouldNotSeeAvailableMushSkill($I);
    }

    public function shouldBeAbleToChooseAllSkillsEventAfterReadingMageBook(FunctionalTester $I): void
    {
        $this->givenPlayerReadsSprinterMageBook();
        $this->givenPlayerHasHumanSkillsAvailable([SkillEnum::TECHNICIAN, SkillEnum::IT_EXPERT, SkillEnum::DETERMINED], $I);

        $this->whenINormalizePlayer();

        $this->thenPlayerShouldSeeAvailableHumanSkills($I);
        $this->thenPlayerShouldHaveFourSkillSlotsAvailable($I);
    }

    private function givenPlayerHasHumanSkillAvailable(SkillEnum $skill, FunctionalTester $I): void
    {
        $this->player->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => $skill]),
        ]);
    }

    private function givenPlayerHasHumanSkillsAvailable(array $skills, FunctionalTester $I): void
    {
        $this->player->getCharacterConfig()->setSkillConfigs([]);
        foreach ($skills as $skill) {
            $this->player->getCharacterConfig()->addSkillConfig(
                $I->grabEntityFromRepository(SkillConfig::class, ['name' => $skill]),
            );
        }
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

    private function givenPlayerReadsSprinterMageBook(): void
    {
        $this->addSkillToPlayer->execute(skill: SkillEnum::SPRINTER, player: $this->player);
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HAS_READ_MAGE_BOOK,
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

    private function thenPlayerShouldNotSeeAvailableHumanSkills(FunctionalTester $I): void
    {
        $I->assertEmpty($this->normalizedPlayer['character']['selectableHumanSkills']);
    }

    private function thenPlayerShouldSeeAvailableHumanSkills(FunctionalTester $I): void
    {
        $I->assertCount(3, $this->normalizedPlayer['character']['selectableHumanSkills']);
    }

    private function thenPlayerShouldHaveFourSkillSlotsAvailable(FunctionalTester $I): void
    {
        $I->assertEquals(4, $this->normalizedPlayer['character']['humanSkillSlots']);
    }
}
