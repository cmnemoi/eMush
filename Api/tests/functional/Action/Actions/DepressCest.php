<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Depress;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class DepressCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Depress $depress;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::DEPRESS->value]);
        $this->depress = $I->grabService(Depress::class);

        $this->addSkillToPlayer(SkillEnum::DISHEARTENING_CONTACT, $I, $this->kuanTi);
    }

    public function shouldMakeTargetLoseTwoMoralePoint(FunctionalTester $I): void
    {
        $this->givenChunHasMoralePoints(10);

        $this->whenKuanTiDepressChun();

        $this->thenChunShouldHaveMoralePoints(8, $I);
    }

    public function shouldPrintACovertLog(FunctionalTester $I): void
    {
        $this->whenKuanTiDepressChun();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: ":mush: **Kuan Ti** se rapproche subrepticement de **Chun**. Après qu'il s'en soit éloigné, les traits de **Chun** se tirent et se creusent...",
            actualRoomLogDto: new RoomLogDto(
                player: $this->kuanTi,
                log: ActionLogEnum::DEPRESS_SUCCESS,
                visibility: VisibilityEnum::COVERT,
                inPlayerRoom: false,
            ),
            I: $I
        );
    }

    private function givenChunHasMoralePoints(int $moralePoints): void
    {
        $this->chun->setMoralPoint($moralePoints);
    }

    private function whenKuanTiDepressChun(): void
    {
        $this->depress->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: $this->chun,
        );
        $this->depress->execute();
    }

    private function thenChunShouldHaveMoralePoints(int $expectedMoralePoints, FunctionalTester $I): void
    {
        $I->assertEquals($expectedMoralePoints, $this->chun->getMoralPoint());
    }
}
