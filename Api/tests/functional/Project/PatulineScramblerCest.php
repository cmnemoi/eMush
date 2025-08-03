<?php

declare(strict_types=1);

namespace Mush\tests\functional\Project;

use Mush\Chat\Entity\Dto\CreateMessage;
use Mush\Chat\Entity\Message;
use Mush\Chat\Services\MessageServiceInterface;
use Mush\Project\Enum\ProjectName;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PatulineScramblerCest extends AbstractFunctionalTest
{
    private MessageServiceInterface $messageService;
    private StatusServiceInterface $statusService;
    private ?Message $message = null;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->messageService = $I->grabService(MessageServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function shouldScrambleExistingMushChannelMessages(FunctionalTester $I): void
    {
        $this->givenAMessageInMushChannel($I);

        $this->whenPatulineScramblerIsFinished($I);

        $this->thenMessageShouldBeScrambled($I);
    }

    public function shouldScrambleNewMushChannelMessages(FunctionalTester $I): void
    {
        $this->whenPatulineScramblerIsFinished($I);

        $this->whenICreateMessageInMushChannel();

        $this->thenMessageShouldBeScrambled($I);
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

    private function givenPlayerIsMush(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function whenPatulineScramblerIsFinished(FunctionalTester $I): void
    {
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::PATULINE_SCRAMBLER),
            author: $this->chun,
            I: $I
        );
    }

    private function whenICreateMessageInMushChannel(): void
    {
        $messageDto = new CreateMessage();
        $messageDto->setChannel($this->mushChannel);
        $messageDto->setMessage('Hello, World!');
        $messageDto->setParent(null);

        $this->message = $this->messageService->createPlayerMessage($this->player, $messageDto);
    }

    private function thenMessageShouldBeScrambled(FunctionalTester $I): void
    {
        $I->assertNotEquals('Hello, World!', $this->message->getMessage());
    }
}
