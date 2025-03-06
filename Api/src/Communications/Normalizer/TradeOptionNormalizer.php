<?php

declare(strict_types=1);

namespace Mush\Communications\Normalizer;

use Mush\Communications\Entity\TradeOption;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class TradeOptionNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly TranslationServiceInterface $translationService,
    ) {}

    public function supportsNormalization(mixed $data, ?string $format = null)
    {
        return $data instanceof TradeOption;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            TradeOption::class => true,
        ];
    }

    public function normalize(mixed $object, ?string $format = null, array $context = [])
    {
        /** @var TradeOption $tradeOption */
        $tradeOption = $object;
        $language = $this->getDaedalusFromContext($context)->getLanguage();

        $requiredAssets = $tradeOption->getRequiredAssets();
        $offeredAssets = $tradeOption->getOfferedAssets();

        $translatedRequiredAssets = [];
        $translatedOfferedAssets = [];

        foreach ($requiredAssets as $requiredAsset) {
            $translatedRequiredAssets[] = $this->translationService->translate(
                key: $requiredAsset->getTranslationKey(),
                parameters: [
                    'quantity' => $requiredAsset->getQuantity(),
                ],
                domain: 'trade',
                language: $language,
            );
        }

        foreach ($offeredAssets as $offeredAsset) {
            $translatedOfferedAssets[] = $this->translationService->translate(
                key: $offeredAsset->getTranslationKey(),
                parameters: [
                    'quantity' => $offeredAsset->getQuantity(),
                ],
                domain: 'trade',
                language: $language,
            );
        }

        $translatedRequiredSkill = '';
        if ($requiredSkill = $tradeOption->getRequiredSkill()->toString()) {
            $translatedRequiredSkill = $this->translationService->translate(
                key: \sprintf('%s.name', $requiredSkill),
                parameters: [],
                domain: 'skill',
                language: $language,
            );
        }

        $translatedName = $translatedRequiredSkill ? \sprintf('[%s] Vendu !', $translatedRequiredSkill) : 'Vendu !';

        return [
            'name' => $translatedName,
            'description' => $this->translationService->translate(
                key: 'trade_option',
                parameters: [
                    'requiredAsset' => implode(', ', $translatedRequiredAssets),
                    'offeredAsset' => implode(', ', $translatedOfferedAssets),
                ],
                domain: 'trade',
                language: $language,
            ),
        ];
    }

    private function getDaedalusFromContext(array $context): Daedalus
    {
        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];

        return $currentPlayer->getDaedalus();
    }
}
