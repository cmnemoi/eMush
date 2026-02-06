<?php

namespace Mush\MetaGame\Normalizer;

use Mush\MetaGame\Entity\News;
use Mush\MetaGame\Entity\Poll\Poll;
use Mush\User\Entity\User;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class NewsNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const string ALREADY_CALLED = 'NEWS_NORMALIZER_ALREADY_CALLED';

    public function __construct(
    ) {}

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof News;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            News::class => false,
        ];
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        /** @var News $news */
        $news = $object;

        $context[self::ALREADY_CALLED] = true;

        $normalizedNews = $this->normalizer->normalize($news, $format, $context);

        if (!\is_array($normalizedNews)) {
            throw new \Exception('normalized news should be an array');
        }

        $poll = $news->getPoll();
        if ($poll) {
            $normalizedPoll = $this->normalizer->normalize($news->getPoll(), $format, $context);
            $normalizedNews['poll'] = $normalizedPoll;
        }

        return $normalizedNews;
    }

    private function normalizeOptions(Poll $poll, User $user): array
    {
        $normalizedOptions = [];

        foreach ($poll->getOptions() as $option) {
            if ($option !== null) {
                $normalizedOptions[] = [
                    'id' => $option->getId(),
                    'name' => $option->getName(),
                    'votes' => $option->getVotes()->count(),
                    'voted' => $option->userAlreadyVoted($user),
                ];
            }
        }

        return $normalizedOptions;
    }
}
