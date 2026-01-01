<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\ReadSchoolbooks;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class ReadSchoolbooksCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private ReadSchoolbooks $readSchoolbooks;

    private GameItem $schoolbookItem;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::READ_SCHOOLBOOKS]);
        $this->readSchoolbooks = $I->grabService(ReadSchoolbooks::class);

        $this->schoolbookItem = $this->createEquipment(ItemEnum::SCHOOLBOOKS, $this->player);
    }

    public function shouldNotBeExecutableWithoutPolyvalent(FunctionalTester $I): void
    {
        $this->whenPlayerTriesToReadSchoolbook();

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::DOES_NOT_HAVE_SKILL_POLYVALENT, $I);
    }

    public function shouldPrintPublicLog(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::POLYVALENT, $I, $this->player);

        $this->whenPlayerReadsSchoolBook();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: '**Chun** se plonge dans un document avec un air intrigué...',
            actualRoomLogDto: new RoomLogDto(
                player: $this->player,
                log: ActionLogEnum::READ_BOOK,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false
            ),
            I: $I
        );
    }

    public function shouldAddLearnedSkillToPlayer(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::POLYVALENT, $I, $this->player);

        $this->whenPlayerReadsSchoolBook();

        $this->thenPlayerShouldHavePolyvalentComponentSkills($I);
    }

    public function shouldDeletePolyvalentSkillAfterLearning(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::POLYVALENT, $I, $this->player);

        $this->whenPlayerReadsSchoolBook();

        $this->thenPlayerShouldNotHavePolyvalentSkill($I);
    }

    public function shouldAddLearnedSkillToAvailableSkills(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::POLYVALENT, $I, $this->player);

        $this->whenPlayerReadsSchoolBook();

        $this->thenPolyvalentComponentSkillsAreAvailableForPlayer($I);
    }

    public function shouldGivePlayerTwoExtraSkillSlots(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::POLYVALENT, $I, $this->player);

        $this->whenPlayerReadsSchoolBook();

        $this->thenPlayerShouldHaveStatusAndSlots($I);
    }

    private function whenPlayerTriesToReadSchoolbook(): void
    {
        $this->readSchoolbooks->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->schoolbookItem,
            player: $this->player,
            target: $this->schoolbookItem,
        );
    }

    private function whenPlayerReadsSchoolbook(): void
    {
        $this->whenPlayerTriesToReadSchoolbook();
        $this->readSchoolbooks->execute();
    }

    private function thenPlayerShouldHavePolyvalentComponentSkills(FunctionalTester $I): void
    {
        $I->assertTrue($this->player->hasSkill(SkillEnum::BOTANIST));
        $I->assertTrue($this->player->hasSkill(SkillEnum::BIOLOGIST));
        $I->assertTrue($this->player->hasSkill(SkillEnum::DIPLOMAT));
    }

    private function thenPlayerShouldHaveStatusAndSlots(FunctionalTester $I): void
    {
        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::HAS_READ_SCHOOLBOOKS_ANNIVERSARY));
        $I->assertEquals(5, $this->player->getHumanSkillSlots());
    }

    private function thenPlayerShouldNotHavePolyvalentSkill(FunctionalTester $I): void
    {
        $I->assertFalse($this->player->hasSkill(SkillEnum::POLYVALENT));
        $I->assertFalse($this->player->getAvailableHumanSkills()->contains($I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::POLYVALENT])));
        $I->assertTrue($this->player->cannotTakeSkill(SkillEnum::POLYVALENT));
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->readSchoolbooks->cannotExecuteReason());
    }

    private function thenPolyvalentComponentSkillsAreAvailableForPlayer(FunctionalTester $I): void
    {
        $I->assertTrue($this->player->getAvailableHumanSkills()->contains($I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::BOTANIST])));
        $I->assertTrue($this->player->getAvailableHumanSkills()->contains($I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::BIOLOGIST])));
        $I->assertTrue($this->player->getAvailableHumanSkills()->contains($I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::DIPLOMAT])));
    }
}
