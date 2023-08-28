<?php

namespace Mush\Player\Normalizer;

use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class ClosedPlayerNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'CLOSED_PLAYER_NORMALIZER_ALREADY_CALLED';

    private CycleServiceInterface $cycleService;
    private PlayerServiceInterface $playerService;
    private TranslationServiceInterface $translationService;
    private GearToolServiceInterface $gearToolService;

    public function __construct(
        CycleServiceInterface $cycleService,
        PlayerServiceInterface $playerService,
        TranslationServiceInterface $translationService,
        GearToolServiceInterface $gearToolService
    ) {
        $this->cycleService = $cycleService;
        $this->playerService = $playerService;
        $this->translationService = $translationService;
        $this->gearToolService = $gearToolService;
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

        $data = $this->normalizer->normalize($object, $format, $context);

        if (!is_array($data)) {
            throw new \Exception('ClosedPlayerNormalizer: data is not an array');
        }

        /** @var \DateTime $startDate */
        $startDate = $closedPlayer->getCreatedAt();

        if ($daedalus->isDaedalusFinished()) {
            $data['characterKey'] = $closedPlayer->getPlayerInfo()->getCharacterConfig()->getCharacterName();
            $data['startCycle'] = $this->cycleService->getInDayCycleFromDate($startDate, $daedalus);
            $data['userId'] = $closedPlayer->getPlayerInfo()->getUser()->getUserId();
            $data['username'] = $closedPlayer->getPlayerInfo()->getUser()->getUsername();
        }

        return $data;
    }
}
