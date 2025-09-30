<?php

declare(strict_types=1);

namespace Mush\Achievement\Normalizer;

use Mush\Achievement\ViewModel\StatisticViewModel;
use Mush\Game\Service\TranslationServiceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class StatisticNormalizer implements NormalizerInterface
{
    public function __construct(private TranslationServiceInterface $translationService) {}

    public function getSupportedTypes(?string $format): array
    {
        return [
            StatisticViewModel::class => true,
        ];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof StatisticViewModel;
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $statisticViewModel = $this->statisticViewModel($object);
        $language = $this->getLanguageFromContext($context);

        return [
            'key' => $statisticViewModel->key,
            'name' => $this->translationService->translate(
                key: \sprintf('%s.name', $statisticViewModel->key),
                parameters: [],
                domain: 'statistics',
                language: $language,
            ),
            'description' => $this->translationService->translate(
                key: \sprintf('%s.description', $statisticViewModel->key),
                parameters: [],
                domain: 'statistics',
                language: $language,
            ),
            'isRare' => $statisticViewModel->isRare,
            'count' => $statisticViewModel->count,
            'formattedCount' => \sprintf('x%s', $statisticViewModel->count),
        ];
    }

    private function statisticViewModel(mixed $object): StatisticViewModel
    {
        return $object instanceof StatisticViewModel ? $object : throw new \InvalidArgumentException('This normalizer only supports StatisticViewModel objects');
    }

    private function getLanguageFromContext(array $context): string
    {
        if (!\array_key_exists('language', $context)) {
            throw new \InvalidArgumentException('Language must be provided in the context for Statistic normalization.');
        }

        return $context['language'];
    }
}
