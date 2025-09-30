<?php

declare(strict_types=1);

namespace Mush\Achievement\Normalizer;

use Mush\Achievement\ViewModel\AchievementViewModel;
use Mush\Game\Service\TranslationServiceInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AchievementNormalizer implements NormalizerInterface
{
    public function __construct(private TranslationServiceInterface $translationService) {}

    public function getSupportedTypes(?string $format): array
    {
        return [
            AchievementViewModel::class => true,
        ];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof AchievementViewModel;
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $achievementViewModel = $this->achievementViewModel($object);
        $language = $this->getLanguageFromContext($context);

        return [
            'key' => $achievementViewModel->key,
            'name' => $this->translationService->translate(
                key: \sprintf('%s.name', $achievementViewModel->key),
                parameters: [],
                domain: 'statistics',
                language: $language,
            ),
            'statisticKey' => $achievementViewModel->statisticKey,
            'statisticName' => \sprintf('%s x%s', $this->translationService->translate(
                key: \sprintf('%s.name', $achievementViewModel->statisticKey),
                parameters: [
                    'count' => $achievementViewModel->threshold,
                ],
                domain: 'statistics',
                language: $language,
            ), $achievementViewModel->threshold),
            'statisticDescription' => $this->translationService->translate(
                key: \sprintf('%s.description', $achievementViewModel->statisticKey),
                parameters: [],
                domain: 'statistics',
                language: $language,
            ),
            'points' => $achievementViewModel->points,
            'formattedPoints' => \sprintf('+%s', $achievementViewModel->points),
            'isRare' => $achievementViewModel->isRare,
        ];
    }

    private function achievementViewModel(mixed $object): AchievementViewModel
    {
        return $object instanceof AchievementViewModel ? $object : throw new \InvalidArgumentException('This normalizer only supports AchievementViewModel objects');
    }

    private function getLanguageFromContext(array $context): string
    {
        if (!\array_key_exists('language', $context)) {
            throw new \InvalidArgumentException('Language must be provided in the context for Achievement normalization.');
        }

        return $context['language'];
    }
}
