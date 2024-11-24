<?php

declare(strict_types=1);

namespace Mush\tests\functional\Project;

use Mush\Communication\Entity\Message;
use Mush\Project\Enum\ProjectName;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PatulineScramblerCest extends AbstractFunctionalTest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
    }

    public function shouldScrambleExistingMushChannelMessages(FunctionalTester $I): void
    {
        $message = $this->givenAMessageInMushChannel($I);

        // when Patuline Scrambler is finished
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::PATULINE_SCRAMBLER),
            author: $this->chun,
            I: $I
        );

        // then the message should be scrambled
        $I->assertNotEquals('Hello, World!', $message->getMessage());
    }

    private function givenAMessageInMushChannel(FunctionalTester $I): Message
    {
        $message = new Message();
        $message
            ->setChannel($this->mushChannel)
            ->setAuthor($this->chun->getPlayerInfo())
            ->setMessage('Hello, World!')
            ->setDay(1)->setCycle(1);
        $I->haveInRepository($message);

        return $message;
    }
}
