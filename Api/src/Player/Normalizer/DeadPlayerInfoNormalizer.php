<?php

namespace Mush\Player\Normalizer;

use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\DeadPlayerInfo;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeadPlayerInfoNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private PlayerServiceInterface $playerService;
    private TranslatorInterface $translator;
    private GearToolServiceInterface $gearToolService;

    public function __construct(
        PlayerServiceInterface $playerService,
        TranslatorInterface $translator,
        GearToolServiceInterface $gearToolService
    ) {
        $this->playerService = $playerService;
        $this->translator = $translator;
        $this->gearToolService = $gearToolService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof DeadPlayerInfo;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var DeadPlayerInfo $deadPlayerInfo */
        $deadPlayerInfo = $object;

        $endCause = $deadPlayerInfo->getEndStatus();

        return [
            'players' => $this->getOtherPlayers($deadPlayerInfo->getPlayer()),
            'endCause' => $this->normalizeEndReason($endCause),
        ];
    }

    private function getOtherPlayers(Player $player): array
    {
        $otherPlayers = [];
        foreach ($player->getDaedalus()->getPlayers() as $otherPlayer) {
            if ($otherPlayer !== $player) {
                $character = $otherPlayer->getCharacterConfig()->getName();

                //TODO add likes
                $normalizedOtherPlayer = [
                    'id' => $player->getId(),
                    'character' => [
                        'key' => $character,
                        'value' => $this->translator->trans($character . '.name', [], 'characters'),
                        'description' => $this->translator->trans($character . '.abstract', [], 'characters'),
                    ],
                ];

                if ($otherPlayer->getGameStatus() !== GameStatusEnum::CURRENT) {
                    $deadPlayerInfo = $this->playerService->findDeadPlayerInfo($otherPlayer);
                    if ($deadPlayerInfo === null) {
                        throw new \LogicException('unable to find deadPlayerInfo');
                    }

                    $endCause = $deadPlayerInfo->getEndStatus();
                    $normalizedOtherPlayer['isDead'] = [
                        'day' => $deadPlayerInfo->getDayDeath(),
                        'cycle' => $deadPlayerInfo->getCycleDeath(),
                        'cause' => $this->normalizeEndReason($endCause),
                    ];
                } else {
                    $normalizedOtherPlayer['isDead'] = false;
                }
                $otherPlayers[] = $normalizedOtherPlayer;
            }
        }

        return $otherPlayers;
    }

    private function normalizeEndReason(string $endCause): array
    {
        return [
            'key' => $endCause,
            'name' => $this->translator->trans($endCause . '.name', [], 'end_cause'),
            'description' => $this->translator->trans($endCause . '.description', [], 'end_cause'),
        ];
    }
}
