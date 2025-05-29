<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Chat\Service;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\MessageModificationEnum;
use Mush\Chat\Services\MessageModifierService;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class MessageModifierServiceCest extends AbstractFunctionalTest
{
    private MessageModifierService $messageModifierService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->messageModifierService = $I->grabService(MessageModifierService::class);
    }

    #[DataProvider('applyModifierEffectsProvider')]
    public function shouldApplyModifierEffectsWithoutAnyError(FunctionalTester $I, Example $example): void
    {
        // given a message
        $message = new Message();
        $message
            ->setChannel($this->publicChannel)
            ->setAuthor($this->chun->getPlayerInfo())
            ->setMessage('Hello, World!')
            ->setDay(1)->setCycle(1);
        $I->haveInRepository($message);

        // when applying paranoia effects
        $message = $this->messageModifierService->applyModifierEffects(
            message: $message,
            player: $this->chun,
            effectName: $example['effectName']
        );

        // then I should not have any errors
    }

    public function shouldApplyPatulineScramblerModifierInMushChannel(FunctionalTester $I): void
    {
        // given a message
        $message = new Message();
        $message
            ->setChannel($this->mushChannel)
            ->setAuthor($this->chun->getPlayerInfo())
            ->setMessage('Hello, World!')
            ->setDay(1)->setCycle(1);
        $I->haveInRepository($message);

        // when applying patuline scrambler effects
        $message = $this->messageModifierService->applyModifierEffects(
            message: $message,
            player: $this->chun,
            effectName: MessageModificationEnum::PATULINE_SCRAMBLER_MODIFICATION
        );

        // then I should have the message modified
        $I->assertNotEquals('Hello, World!', $message->getMessage());
    }

    public function shouldNotApplyPatulineScramblerModifierInOtherChannels(FunctionalTester $I): void
    {
        // given a message
        $message = new Message();
        $message
            ->setChannel($this->publicChannel)
            ->setAuthor($this->chun->getPlayerInfo())
            ->setMessage('Hello, World!')
            ->setDay(1)->setCycle(1);
        $I->haveInRepository($message);

        // when applying patuline scrambler effects
        $message = $this->messageModifierService->applyModifierEffects(
            message: $message,
            player: $this->chun,
            effectName: MessageModificationEnum::PATULINE_SCRAMBLER_MODIFICATION
        );

        // then I should not have the message modified
        $I->assertEquals('Hello, World!', $message->getMessage());
    }

    public function deafSpeakShouldUppercaseAllUnicodeCharacters(FunctionalTester $I): void
    {
        // given a message
        $message = new Message();
        $message
            ->setChannel($this->publicChannel)
            ->setAuthor($this->chun->getPlayerInfo())
            ->setMessage('ça va péter!')
            ->setDay(1)->setCycle(1);
        $I->haveInRepository($message);

        // when applying deaf speak effects
        $message = $this->messageModifierService->applyModifierEffects(
            message: $message,
            player: $this->chun,
            effectName: MessageModificationEnum::DEAF_SPEAK
        );

        // then I should have the message modified
        $I->assertEquals('ÇA VA PÉTER!', $message->getMessage());
    }

    private function applyModifierEffectsProvider(): array
    {
        return [
            [
                'effectName' => MessageModificationEnum::COPROLALIA_MESSAGES,
            ],
            [
                'effectName' => MessageModificationEnum::PARANOIA_MESSAGES,
            ],
            [
                'effectName' => MessageModificationEnum::PARANOIA_DENIAL,
            ],
            [
                'effectName' => MessageModificationEnum::DEAF_LISTEN,
            ],
            [
                'effectName' => MessageModificationEnum::DEAF_SPEAK,
            ],
            [
                'effectName' => MessageModificationEnum::PATULINE_SCRAMBLER_MODIFICATION,
            ],
        ];
    }
}
