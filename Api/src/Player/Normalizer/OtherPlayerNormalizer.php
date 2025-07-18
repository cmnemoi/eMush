<?php

namespace Mush\Player\Normalizer;

use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Normalizer\ActionHolderNormalizerTrait;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\MetaGame\Entity\Skin\SkinSlot;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OtherPlayerNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use ActionHolderNormalizerTrait;
    use NormalizerAwareTrait;

    private TranslationServiceInterface $translationService;
    private GearToolServiceInterface $gearToolService;

    public function __construct(
        TranslationServiceInterface $translationService,
        GearToolServiceInterface $gearToolService
    ) {
        $this->translationService = $translationService;
        $this->gearToolService = $gearToolService;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        $currentPlayer = $context['currentPlayer'] ?? null;

        return $data instanceof Player && $data !== $currentPlayer;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Player::class => false,
        ];
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        /** @var Player $player */
        $player = $object;

        $context[$player->getClassName()] = $player;

        $language = $player->getDaedalus()->getLanguage();

        $character = $player->getName();

        $playerData = [
            'id' => $player->getId(),
            'character' => [
                'key' => $character,
                'value' => $this->translationService->translate(
                    $character . '.name',
                    [],
                    'characters',
                    $player->getDaedalus()->getLanguage()
                ),
                'description' => $this->translationService->translate(
                    $character . '.description',
                    [],
                    'characters',
                    $player->getDaedalus()->getLanguage()
                ),
            ],
            'skills' => $this->getNormalizedPlayerSkills($player, $format, $context),
            'skins' => $this->normalizeSkins($player),
        ];

        if (isset($context['currentPlayer'])) {
            /** @var Player $currentPlayer */
            $currentPlayer = $context['currentPlayer'];
            $statuses = [];
            foreach ($player->getStatuses() as $status) {
                $normedStatus = $this->normalizer->normalize($status, $format, array_merge($context, ['player' => $player]));
                if (\is_array($normedStatus) && \count($normedStatus) > 0) {
                    $statuses[] = $normedStatus;
                }
            }

            // if current player is mush add spores info
            if ($currentPlayer->isMush() && !$player->hasStatus(PlayerStatusEnum::IMMUNIZED)) {
                $normedSpores = [
                    'key' => PlayerStatusEnum::SPORES,
                    'name' => $this->translationService->translate(PlayerStatusEnum::SPORES . '.name', [], 'status', $language),
                    'description' => $this->translationService->translate(PlayerStatusEnum::SPORES . '.description', [], 'status', $language),
                    'charge' => $player->getSpores(),
                ];
                $statuses[] = $normedSpores;
            }

            $titles = [];
            foreach ($player->getTitles() as $title) {
                $normedTitle = [
                    'id' => $title,
                    'name' => $this->translationService->translate($title . '.name', [], 'player', $language),
                    'description' => $this->translationService->translate($title . '.desc', [], 'player', $language),
                ];
                $titles[] = $normedTitle;
            }

            $playerData['statuses'] = $statuses;
            $playerData['skills'] = $this->getNormalizedPlayerSkills($player, $format, $context);
            $playerData['titles'] = $titles;
            $playerData['actions'] = $this->getNormalizedActions($player, ActionHolderEnum::OTHER_PLAYER, $currentPlayer, $format, $context);
        }

        return $playerData;
    }

    private function getNormalizedPlayerSkills(Player $player, ?string $format = null, array $context = []): array
    {
        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];
        $skillsToNormalize = $currentPlayer->isMush() ? $player->getMushAndHumanSkills() : $player->getHumanSkills()->getSortedBy('createdAt');

        $normalizedSkills = [];
        foreach ($skillsToNormalize as $skill) {
            $normalizedSkills[] = $this->normalizer->normalize($skill, $format, $context);
        }

        return $normalizedSkills;
    }

    private function normalizeSkins(Player $player): array
    {
        $skins = [];

        /** @var SkinSlot $slot */
        foreach ($player->getSkinSlots() as $slot) {
            if (($skin = $slot->getSkin()) !== null) {
                $skins[] = [
                    'skinSlotName' => $slot->getName(),
                    'skinName' => $skin->getName(),
                ];
            }
        }

        return $skins;
    }
}
