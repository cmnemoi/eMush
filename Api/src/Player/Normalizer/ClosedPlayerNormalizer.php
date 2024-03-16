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

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof ClosedPlayer;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var ClosedPlayer $closedPlayer */
        $closedPlayer = $object;

        $daedalus = $closedPlayer->getClosedDaedalus();

        $context[self::ALREADY_CALLED] = true;

        /** @var array $data */
        $data = $this->normalizer->normalize($object, $format, $context);

        if ($daedalus->isDaedalusFinished()) {
            /** @var \DateTime $createdAt */
            $createdAt = $closedPlayer->getCreatedAt();
            /** @var \DateTime $finishedAt */
            $finishedAt = $closedPlayer->getFinishedAt();

            $data['cyclesSurvived'] = $this->cycleService->getNumberOfCycleElapsed(
                start: $createdAt,
                end: $finishedAt,
                daedalusInfo: $closedPlayer->getClosedDaedalus()->getDaedalusInfo()
            );
            $data['daysSurvived'] = intval($data['cyclesSurvived'] / $daedalus->getDaedalusInfo()->getGameConfig()->getDaedalusConfig()->getCyclePerGameDay());

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
}
