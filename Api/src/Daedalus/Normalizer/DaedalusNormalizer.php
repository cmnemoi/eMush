<?php

namespace Mush\Daedalus\Normalizer;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\GameConfigServiceInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class DaedalusNormalizer implements ContextAwareNormalizerInterface
{
    private CycleServiceInterface $cycleService;
    private GameConfig $gameConfig;

    public function __construct(
        CycleServiceInterface $cycleService,
        GameConfigServiceInterface $gameConfigService
    ) {
        $this->cycleService = $cycleService;
        $this->gameConfig = $gameConfigService->getConfig();
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
        return [
                'id' => $object->getId(),
                'cycle' => $object->getCycle(),
                'day' => $object->getDay(),
                'oxygen' => $object->getOxygen(),
                'fuel' => $object->getFuel(),
                'hull' => $object->getHull(),
                'shield' => $object->getShield(),
                'nextCycle' => $this->cycleService->getDateStartNextCycle($object)->format(\DateTime::ATOM),
            ];
    }
}
