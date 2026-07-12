<?php

declare(strict_types=1);

namespace Mush\MetaGame\Normalizer;

use Mush\MetaGame\Entity\News;
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
        $context['groups'] = array_merge($context['groups'] ?? [], ['news_read']);

        $normalizedNews = $this->normalizer->normalize($news, $format, $context);

        if (!\is_array($normalizedNews)) {
            throw new \Exception('normalized news should be an array');
        }

        $poll = $news->getPoll();
        if ($poll) {
            $normalizedPoll = $this->normalizer->normalize($news->getPoll(), $format, $context);
            if ($normalizedPoll) {
                $normalizedNews['poll'] = $normalizedPoll;
            } else {
                unset($normalizedNews['poll']);
            }
        }

        return $normalizedNews;
    }
}
