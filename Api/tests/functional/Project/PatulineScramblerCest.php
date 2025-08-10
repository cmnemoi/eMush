<?php

declare(strict_types=1);

namespace Mush\tests\functional\Project;

use Doctrine\Common\Collections\Collection;
use Mush\Chat\Entity\Message;
use Mush\Chat\Services\MessageServiceInterface;
use Mush\Project\Enum\ProjectName;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PatulineScramblerCest extends AbstractFunctionalTest
{
    private MessageServiceInterface $messageService;
    private ?Message $message = null;
    private Collection $fetchedMessages;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->messageService = $I->grabService(MessageServiceInterface::class);
    }

    public function shouldScrambleExistingMushChannelMessages(FunctionalTester $I): void
    {
        $this->givenAMessageInMushChannel($I);

        $this->givenPatulineScramblerIsFinished($I);

        $this->whenIFetchMushChannelMessages();

        $this->thenMessagesShouldBeScrambled($I);
    }

    public function shouldNotScrambleSystemMessages(FunctionalTester $I): void
    {
        $this->givenASystemMessageInMushChannel($I);

        $this->givenPatulineScramblerIsFinished($I);

        $this->whenIFetchMushChannelMessages();

        $this->thenMessagesShouldNotBeScrambled($I);
    }

    private function givenASystemMessageInMushChannel(FunctionalTester $I): void
    {
        $this->message = new Message();
        $this->message
            ->setChannel($this->mushChannel)
            ->setMessage('Hello, World!')
            ->setDay(1)->setCycle(1);

        $I->haveInRepository($this->message);
    }

    private function givenAMessageInMushChannel(FunctionalTester $I): void
    {
        $this->message = new Message();
        $this->message
            ->setChannel($this->mushChannel)
            ->setAuthor($this->chun->getPlayerInfo())
            ->setMessage('Hello, World!')
            ->setDay(1)->setCycle(1);

        $I->haveInRepository($this->message);
    }

    private function givenPatulineScramblerIsFinished(FunctionalTester $I): void
    {
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::PATULINE_SCRAMBLER),
            author: $this->chun,
            I: $I
        );
    }

    private function whenIFetchMushChannelMessages(): void
    {
        $this->fetchedMessages = $this->messageService->getChannelMessages(
            player: $this->player,
            channel: $this->mushChannel,
            timeLimit: new \DateInterval('P1Y')
        );
    }

    private function thenMessagesShouldBeScrambled(FunctionalTester $I): void
    {
        foreach ($this->fetchedMessages as $message) {
            $I->assertNotEquals('Hello, World!', $message->getMessage());
        }
    }

    private function thenMessagesShouldNotBeScrambled(FunctionalTester $I): void
    {
        foreach ($this->fetchedMessages as $message) {
            $I->assertEquals('Hello, World!', $message->getMessage());
        }
    }
}
