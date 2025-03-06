<?php

declare(strict_types=1);

namespace Mush\Communications\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Communications\Entity\Trade;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class TradeNormalizer implements NormalizerAwareInterface, NormalizerInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private readonly TranslationServiceInterface $translationService,
    ) {}

    public function supportsNormalization(mixed $data, ?string $format = null): bool
    {
        return $data instanceof Trade;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Trade::class => true,
        ];
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        /** @var Trade $trade */
        $trade = $object;
        $daedalus = $this->getDaedalusFromContext($context);

        return [
            'id' => $trade->getId(),
            'description' => $this->translationService->translate(
                key: $trade->getName()->toString(),
                parameters: [],
                domain: 'trade',
                language: $daedalus->getLanguage(),
            ),
            'options' => $this->normalizeTradeOptions($trade->getTradeOptions(), $format, $context),
        ];
    }

    private function getDaedalusFromContext(array $context): Daedalus
    {
        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];

        return $currentPlayer->getDaedalus();
    }

    private function normalizeTradeOptions(ArrayCollection $tradeOptions, ?string $format, array $context): array
    {
        $normalizedTradeOptions = [];

        foreach ($tradeOptions as $tradeOption) {
            $normalizedTradeOptions[] = $this->normalizer->normalize($tradeOption, $format, $context);
        }

        return $normalizedTradeOptions;
    }
}
