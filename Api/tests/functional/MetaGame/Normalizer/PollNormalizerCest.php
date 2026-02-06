<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Exploration\Normalizer;

use ApiPlatform\Api\IriConverterInterface;
use Mush\Exploration\Entity\Exploration;
use Mush\MetaGame\Normalizer\PollNormalizer;
use Mush\MetaGame\Service\PollService;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class PollNormalizerCest extends AbstractFunctionalTest
{
    private PollNormalizer $pollNormalizer;
    private NormalizerInterface $normalizer;
    private PollService $pollService;

    private IriConverterInterface $iriConverter;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->pollService = $I->grabService(PollService::class);

        $this->pollNormalizer = $I->grabService(PollNormalizer::class);
        $this->normalizer = $I->grabService(NormalizerInterface::class);
        $this->iriConverter = $I->grabService(IriConverterInterface::class);

        $this->pollNormalizer->setNormalizer($this->normalizer);
    }

    public function testNormalizeForTraitorPlayer(FunctionalTester $I): void
    {
        $poll = $this->pollService->createPoll('Poll test', 3, false);

        $option1 = $this->pollService->createOption($poll, 'Option 1');
        $option2 = $this->pollService->createOption($poll, 'Option 2');
        $option3 = $this->pollService->createOption($poll, 'Option 3');
        $this->pollService->vote($poll, $option1, $this->chun->getUser());

        // when the exploration is normalized for Chun
        $normalizedPoll = $this->pollNormalizer->normalize(
            $poll,
            format: null,
            context: ['user' => $this->chun->getUser()]
        );

        // then Chun should see the next sector as revealed and highlighted
        $I->assertEquals(
            expected: [
                '@id' => $this->iriConverter->getIriFromResource($poll),
                'id' => $poll->getId(),
                'title' => 'Poll test',
                'voteCount' => 1,
                'canVote' => true,
                'options' => [
                    0 => [
                        'name' => 'Option 1',
                        'votes' => 1,
                        'voted' => true,
                        'id' => $option1->getId(),
                    ],
                    1 => [
                        'name' => 'Option 2',
                        'votes' => 0,
                        'voted' => false,
                        'id' => $option2->getId(),
                    ],
                    2 => [
                        'name' => 'Option 3',
                        'votes' => 0,
                        'voted' => false,
                        'id' => $option3->getId(),
                    ],
                ],
                'remainingVotes' => 2,
                'voted' => true,
                'isClosed' => false,
            ],
            actual: $normalizedPoll
        );
    }
}
