<?php

declare(strict_types=1);

namespace Mush\Communications\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Communications\Entity\TradeAsset;
use Mush\Communications\Entity\TradeOption;
use Mush\Communications\Service\AreTradeOptionConditionsAreMetService;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Skill\Enum\SkillEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class TradeOptionNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly AreTradeOptionConditionsAreMetService $areTradeOptionConditionsAreMet,
        private readonly TranslationServiceInterface $translationService,
    ) {}

    public function supportsNormalization(mixed $data, ?string $format = null): bool
    {
        return $data instanceof TradeOption;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            TradeOption::class => true,
        ];
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): ?array
    {
        /** @var TradeOption $tradeOption */
        $tradeOption = $object;
        $currentPlayer = $this->getCurrentPlayerFromContext($context);
        $language = $this->getLanguageFromContext($context);
        $requiredSkill = $tradeOption->getRequiredSkill();

        if (!$this->isTradeOptionAvailable($requiredSkill, $currentPlayer)) {
            return null;
        }

        $translatedRequiredAssets = $this->translateAssets(
            $tradeOption->getRequiredAssets(),
            $language
        );

        $translatedOfferedAssets = $this->translateAssets(
            $tradeOption->getOfferedAssets(),
            $language
        );

        return [
            'id' => $tradeOption->getId(),
            'name' => $this->createTranslatedName($tradeOption, $language),
            'description' => $this->translationService->translate(
                key: 'trade_option',
                parameters: [
                    'requiredAsset' => implode(', ', $translatedRequiredAssets),
                    'offeredAsset' => implode(', ', $translatedOfferedAssets),
                ],
                domain: 'trade',
                language: $language,
            ),
            'tradeConditionsAreNotMet' => !$this->areTradeOptionConditionsAreMet->execute($currentPlayer, $tradeOption->getId()) ?
                $this->translationService->translate(
                    key: 'trade_conditions_not_met',
                    parameters: [],
                    domain: 'trade',
                    language: $language,
                )
                : '',
        ];
    }

    private function isTradeOptionAvailable(SkillEnum $requiredSkill, Player $currentPlayer): bool
    {
        if ($requiredSkill->isNull()) {
            return true;
        }

        return $currentPlayer->getAlivePlayersInRoom()->hasPlayerWithSkill($requiredSkill);
    }

    /**
     * @psalm-param ArrayCollection<array-key, TradeAsset> $assets
     */
    private function translateAssets(ArrayCollection $assets, string $language): array
    {
        $translatedAssets = [];

        foreach ($assets as $asset) {
            $translatedAssets[] = $this->translationService->translate(
                key: $asset->getTranslationKey(),
                parameters: [
                    'quantity' => $asset->getQuantity(),
                ],
                domain: 'trade',
                language: $language,
            );
        }

        return $translatedAssets;
    }

    private function createTranslatedName(TradeOption $tradeOption, string $language): string
    {
        $translatedRequiredSkill = $this->translateSkillName($tradeOption->getRequiredSkill(), $language);
        $translatedName = $this->translationService->translate(
            key: $tradeOption->getName(),
            parameters: [],
            domain: 'trade',
            language: $language,
        );

        return $translatedRequiredSkill
            ? \sprintf('[%s] %s', $translatedRequiredSkill, $translatedName)
            : $translatedName;
    }

    private function translateSkillName(SkillEnum $skillEnum, string $language): string
    {
        if ($skillEnum->isNull()) {
            return $skillEnum->toString();
        }

        return $this->translationService->translate(
            key: \sprintf('%s.name', $skillEnum->toString()),
            parameters: [],
            domain: 'skill',
            language: $language,
        );
    }

    private function getCurrentPlayerFromContext(array $context): Player
    {
        // @var Player $currentPlayer
        return $context['currentPlayer'];
    }

    private function getLanguageFromContext(array $context): string
    {
        $currentPlayer = $this->getCurrentPlayerFromContext($context);

        return $currentPlayer->getDaedalus()->getLanguage();
    }
}
