<?php

namespace Mush\Player\Normalizer;

use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\ClosedPlayer;
use Mush\User\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ClosedPlayerNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'CLOSED_PLAYER_NORMALIZER_ALREADY_CALLED';

    private CycleServiceInterface $cycleService;
    private TranslationServiceInterface $translationService;
    private Security $security;

    public function __construct(
        CycleServiceInterface $cycleService,
        TranslationServiceInterface $translationService,
        Security $security
    ) {
        $this->cycleService = $cycleService;
        $this->translationService = $translationService;
        $this->security = $security;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof ClosedPlayer;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            ClosedPlayer::class => false,
        ];
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        /** @var ClosedPlayer $closedPlayer */
        $closedPlayer = $object;

        $daedalus = $closedPlayer->getClosedDaedalus();

        $context[self::ALREADY_CALLED] = true;
        $context['language'] = $closedPlayer->getLanguage();

        /** @var array $data */
        $data = $this->normalizer->normalize($object, $format, $context);

        if ($daedalus->isDaedalusFinished()) {
            /** @var \DateTime $createdAt */
            $createdAt = $closedPlayer->getCreatedAt();

            /** @var \DateTime $finishedAt */
            $finishedAt = $closedPlayer->getFinishedAt();

            $cyclesSurvived = $this->cycleService->getNumberOfCycleElapsed(
                start: $createdAt,
                end: $finishedAt,
                daedalusInfo: $closedPlayer->getClosedDaedalus()->getDaedalusInfo()
            );
            $data['daysSurvived'] = (int) ($cyclesSurvived / $daedalus->getDaedalusInfo()->getGameConfig()->getDaedalusConfig()->getCyclePerGameDay());
            $data['cyclesSurvived'] = $cyclesSurvived % $daedalus->getDaedalusInfo()->getGameConfig()->getDaedalusConfig()->getCyclePerGameDay();
            $data['score'] = $closedPlayer->getTriumph() ?? $cyclesSurvived;
            $data['triumph'] = $closedPlayer->getTriumph();
            $data['triumphGains'] = $this->normalizer->normalize($closedPlayer->getTriumphGains(), $format, $context);
            $data['playerHighlights'] = $this->getNormalizedPlayerHighlights($closedPlayer, $format, $context);

            // Tell moderators if closed player end message is hidden
            /** @var ?User $user */
            $user = $this->security->getUser();
            if ($user?->isModerator()) {
                $data['messageIsHidden'] = $closedPlayer->messageIsHidden();
                $data['messageIsEdited'] = $closedPlayer->messageIsEdited();
            }

            // Do not normalize hidden end message except for their author and moderators
            if (
                $closedPlayer->messageIsHidden()
                && ($user !== $closedPlayer->getUser() && !$user?->isModerator())
            ) {
                $data['message'] = null;
            }
        }

        return $data;
    }

    private function getNormalizedPlayerHighlights(ClosedPlayer $closedPlayer, ?string $format, array $context): array
    {
        $playerHighlights = $closedPlayer->getPlayerHighlights();
        $normalizedPlayerHighlights = [];

        foreach ($playerHighlights as $playerHighlight) {
            $normalizedPlayerHighlight = $this->normalizer->normalize($playerHighlight, $format, $context);

            // Skip if the player highlight is already in the list (e.g. Upgrade drone which are different actions for each upgrade)
            if (\in_array($normalizedPlayerHighlight, $normalizedPlayerHighlights, true)) {
                continue;
            }

            // Skip if the player highlight has not been translated (e.g. failed actions)
            if ($normalizedPlayerHighlight === $playerHighlight->toTranslationKey()) {
                continue;
            }

            $normalizedPlayerHighlights[] = $normalizedPlayerHighlight;
        }

        return $normalizedPlayerHighlights;
    }
}
