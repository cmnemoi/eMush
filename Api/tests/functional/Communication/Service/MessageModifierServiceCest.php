<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Communication\Service;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\MessageModificationEnum;
use Mush\Communication\Services\MessageModifierService;
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
        ];
    }
}
