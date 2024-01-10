<?php

namespace Mush\Player\Normalizer;

use Doctrine\Common\Collections\Collection;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Action\Normalizer\ActionHolderNormalizerTrait;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
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

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        $currentPlayer = $context['currentPlayer'] ?? null;

        return $data instanceof Player && $data !== $currentPlayer;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Player $player */
        $player = $object;

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
                'skills' => $player->getPlayerInfo()->getCharacterConfig()->getSkills(),
            ],
        ];

        if (isset($context['currentPlayer'])) {
            /** @var Player $currentPlayer */
            $currentPlayer = $context['currentPlayer'];
            $statuses = [];
            foreach ($player->getStatuses() as $status) {
                $normedStatus = $this->normalizer->normalize($status, $format, array_merge($context, ['player' => $player]));
                if (is_array($normedStatus) && count($normedStatus) > 0) {
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
            $playerData['skills'] = $player->getSkills();
            $playerData['titles'] = $titles;
            $playerData['actions'] = $this->getActions($player, $format, $context);
        }

        return $playerData;
    }

    private function getActions(Player $player, ?string $format, array $context): array
    {
        $contextualActions = $this->getContextActions($context['currentPlayer'])->toArray();
        $targetActions = $player->getTargetActions()->toArray();

        $actionsToNormalize = array_merge($contextualActions, $targetActions);

        $actions = [];

        /** @var Action $action */
        foreach ($actionsToNormalize as $action) {
            $normedAction = $this->normalizer->normalize($action, $format, array_merge($context, ['player' => $player]));
            if (is_array($normedAction) && count($normedAction) > 0) {
                $actions[] = $normedAction;
            }
        }

        $actions = $this->getNormalizedActionsSortedBy('name', $actions);
        $actions = $this->getNormalizedActionsSortedBy('actionPointCost', $actions);

        return $actions;
    }

    private function getContextActions(Player $currentPlayer): Collection
    {
        $scope = [ActionScopeEnum::OTHER_PLAYER];

        return $this->gearToolService->getActionsTools($currentPlayer, $scope);
    }
}
