<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Disease\SymptomHandler;

use Mush\Disease\Enum\SymptomEnum;
use Mush\Disease\SymptomHandler\Biting;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Enum\ModifierPriorityEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class BitingCest extends AbstractFunctionalTest
{
    private Biting $bitingSymptom;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->bitingSymptom = $I->grabService(Biting::class);
    }

    public function testBitingDoNotThrowExceptionIfPlayerIsAlone(FunctionalTester $I)
    {
        // given I have only one player in laboratory
        $this->player2->changePlace($this->daedalus->getPlaceByName(RoomEnum::SPACE));
        $I->assertEquals(1, $this->daedalus->getPlaceByName(RoomEnum::LABORATORY)->getNumberOfPlayersAlive());

        // when I apply biting symptom to player1
        $this->bitingSymptom->applyEffects(
            player: $this->player1,
            priority: ModifierPriorityEnum::getPriorityAsInteger(ModifierPriorityEnum::PREVENT_EVENT),
            tags: ['test'],
            time: new \DateTime()
        );

        // then no exception is thrown
        $I->expect('no exception is thrown');

        // then no biting log is created
        $I->dontSeeInRepository(RoomLog::class, [
            'place' => $this->daedalus->getPlaceByName(RoomEnum::LABORATORY),
            'log' => SymptomEnum::BITING,
        ]);
    }

    public function testBitingCorrectlyPrintsBittenPlayerName(FunctionalTester $I)
    {
        // given I have two players in laboratory
        $I->assertEquals(2, $this->daedalus->getPlaceByName(RoomEnum::LABORATORY)->getNumberOfPlayersAlive());

        // when I apply biting symptom to player1
        $this->bitingSymptom->applyEffects(
            player: $this->player1,
            priority: ModifierPriorityEnum::getPriorityAsInteger(ModifierPriorityEnum::PREVENT_EVENT),
            tags: ['test'],
            time: new \DateTime()
        );

        // then I see a correctly parametered biting log
        /** @var RoomLog $bitingLog */
        $bitingLog = $I->grabEntityFromRepository(RoomLog::class, [
            'place' => $this->daedalus->getPlaceByName(RoomEnum::LABORATORY)->getName(),
            'log' => SymptomEnum::BITING,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        $bitingLogParameters = $bitingLog->getParameters();
        $I->assertEquals(
            expected: $this->player2->getLogName(),
            actual: $bitingLogParameters['target_character'],
        );
        $I->assertEquals(
            expected: $this->player1->getLogName(),
            actual: $bitingLogParameters['character'],
        );
    }
}
