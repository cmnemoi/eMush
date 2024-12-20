<?php

declare(strict_types=1);

namespace Mush\tests\functional\Communication\Service;

use Mush\Communication\Entity\Dto\CreateMessage;
use Mush\Communication\Entity\Message;
use Mush\Communication\Services\MessageServiceInterface;
use Mush\Disease\Enum\InjuryEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class MessageServiceCest extends AbstractFunctionalTest
{
    private MessageServiceInterface $messageService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->messageService = $I->grabService(MessageServiceInterface::class);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
    }

    public function shouldCreateMessageInMushChannelForMutePlayer(FunctionalTester $I): void
    {
        $this->givenPlayerHasTornTongue();

        $this->whenICreateMessageInMushChannel();

        $this->thenMessageShouldBeCreatedInMushChannel($I);
    }

    private function givenPlayerHasTornTongue(): void
    {
        $this->playerDiseaseService->createDiseaseFromName(
            InjuryEnum::TORN_TONGUE,
            $this->player,
            [],
        );
    }

    private function whenICreateMessageInMushChannel(): void
    {
        $messageDto = new CreateMessage();
        $messageDto->setChannel($this->mushChannel);
        $messageDto->setMessage('test');
        $messageDto->setParent(null);
        $messageDto->setPlayer($this->player);

        $this->messageService->createPlayerMessage(
            player: $this->player,
            createMessage: $messageDto
        );
    }

    private function thenMessageShouldBeCreatedInMushChannel(FunctionalTester $I): void
    {
        $I->seeInRepository(Message::class, [
            'channel' => $this->mushChannel,
            'message' => 'test',
        ]);
    }
}
