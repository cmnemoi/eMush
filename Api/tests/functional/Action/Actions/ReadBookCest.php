<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\ReadBook;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class ReadBookCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private ReadBook $readBook;
    private GameItem $magebook;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::READ_BOOK->value]);
        $this->readBook = $I->grabService(ReadBook::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $this->givenPlayerHasASprinterMageBook();
    }

    public function shouldAddBookSkillToPlayer(FunctionalTester $I): void
    {
        $this->whenPlayerReadsBook();

        $this->thenPlayerShouldHaveSprinterSkill($I);
    }

    public function shouldPrintPublicLog(FunctionalTester $I): void
    {
        $this->whenPlayerReadsBook();

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

    public function shouldPrintPrivateLog(FunctionalTester $I): void
    {
        $this->whenPlayerReadsBook();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Vous avez appris la compétence **Sprinter** ! Faites en bon usage !',
            actualRoomLogDto: new RoomLogDto(
                player: $this->player,
                log: LogEnum::LEARNED_SKILL,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I
        );
    }

    public function shouldBeExecutableOncePerPlayer(FunctionalTester $I): void
    {
        $this->givenPlayerHasASprinterMageBook();

        $this->givenPlayerReadsBook();

        $this->givenPlayerHasATechnicianMageBook();

        $this->whenPlayerTriesToReadBook();

        $this->thenActionShouldNotBeExecutableWithMessage(
            message: ActionImpossibleCauseEnum::MAGE_BOOK_ALREADY_HAVE_READ,
            I: $I,
        );
    }

    public function shouldNotBeExecutableIfPlayerAlreadyHasMageBookSkill(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::SPRINTER, $I);

        $this->givenPlayerHasASprinterMageBook();

        $this->whenPlayerTriesToReadBook();

        $this->thenActionShouldNotBeExecutableWithMessage(
            message: ActionImpossibleCauseEnum::MAGE_BOOK_ALREADY_HAVE_SKILL,
            I: $I,
        );
    }

    public function polyvalentShouldNotBeAbleToReadDiplomatMageBook(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::POLYVALENT, $I);

        $this->givenPlayerHasADiplomatMageBook();

        $this->whenPlayerTriesToReadBook();

        $this->thenActionShouldNotBeExecutableWithMessage(
            message: ActionImpossibleCauseEnum::MAGE_BOOK_ALREADY_HAVE_SKILL,
            I: $I,
        );
    }

    private function givenPlayerHasASprinterMageBook(): void
    {
        $this->magebook = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: 'apprentron_sprinter',
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerReadsBook(): void
    {
        $this->readBook->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->magebook,
            player: $this->player,
            target: $this->magebook
        );
        $this->readBook->execute();
    }

    private function givenPlayerHasATechnicianMageBook(): void
    {
        $this->magebook = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: 'apprentron_technician',
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerHasADiplomatMageBook(): void
    {
        $this->magebook = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: 'apprentron_diplomat',
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function whenPlayerReadsBook(): void
    {
        $this->readBook->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->magebook,
            player: $this->player,
            target: $this->magebook
        );
        $this->readBook->execute();
    }

    private function whenPlayerTriesToReadBook(): void
    {
        $this->readBook->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->magebook,
            player: $this->player,
            target: $this->magebook
        );
    }

    private function thenPlayerShouldHaveSprinterSkill(FunctionalTester $I): void
    {
        $I->assertTrue($this->player->hasSkill(SkillEnum::SPRINTER));
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->readBook->cannotExecuteReason());
    }
}
