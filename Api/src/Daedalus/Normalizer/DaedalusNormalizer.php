<?php

namespace Mush\Daedalus\Normalizer;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Status\Enum\StatusEnum;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DaedalusNormalizer implements ContextAwareNormalizerInterface
{
    private CycleServiceInterface $cycleService;
    private TranslatorInterface $translator;

    public function __construct(
        CycleServiceInterface $cycleService,
        TranslatorInterface $translator
    ) {
        $this->cycleService = $cycleService;
        $this->translator = $translator;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Daedalus;
    }

    /**
     * @param mixed $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Daedalus $daedalus */
        $daedalus = $object;
        $gameConfig = $daedalus->getGameConfig();

        return [
                'id' => $object->getId(),
                'cycle' => $object->getCycle(),
                'day' => $object->getDay(),
                'oxygen' => $object->getOxygen(),
                'fuel' => $object->getFuel(),
                'hull' => $object->getHull(),
                'shield' => $object->getShield(),
                'nextCycle' => $this->cycleService->getDateStartNextCycle($object)->format(\DateTime::ATOM),
                'cryogenizedPlayers' => $gameConfig->getCharactersConfig()->count() - $daedalus->getPlayers()->count(),
                'humanPlayerAlive' => $daedalus->getPlayers()->getHumanPlayer()->getPlayerAlive()->count(),
                'humanPlayerDead' => $daedalus->getPlayers()->getHumanPlayer()->getPlayerDead()->count(),
                'mushPlayerAlive' => $daedalus->getPlayers()->getMushPlayer()->getPlayerAlive()->count(),
                'mushPlayerDead' => $daedalus->getPlayers()->getMushPlayer()->getPlayerDead()->count(),
                'minimap' => $this->getMinimap($daedalus),
            ];
    }

    public function getMinimap($daedalus): array
    {
        $minimap = [];
        foreach ($daedalus->getRooms() as $room) {
            $minimap[$room->getName()] = [
                'name' => $this->translator->trans($room->getName() . '.name', [], 'rooms'),
                'players' => $room->getPlayers()->count(),
                'fire' => $room->getStatusByName(StatusEnum::FIRE) !== null,
            ];

            //@TODO add project fire detector, anomaly detector doors detectors and actopi protocol
        }

        return $minimap;
    }
}
