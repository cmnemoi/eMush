<?php

namespace Mush\Daedalus\Normalizer;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class DaedalusNormalizer implements ContextAwareNormalizerInterface
{
    private GameConfig $gameConfig;

    public function __construct(GameConfigServiceInterface $gameConfigService)
    {
        $this->gameConfig = $gameConfigService->getConfig();
    }


    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Daedalus;
    }

    /**
     * @param Daedalus $daedalus
     * @param string|null $format
     * @param array $context
     * @return array
     */
    public function normalize($daedalus, string $format = null, array $context = [])
    {
            return [
                'id' => $daedalus->getId(),
                'cycle' => ($daedalus->getCycle()-1 % $this->gameConfig->getNumberOfCyclePerDay())+1,
                'day' => floor($daedalus->getCycle() / $this->gameConfig->getNumberOfCyclePerDay()),
                'oxygen' => $daedalus->getOxygen(),
                'fuel' => $daedalus->getFuel(),
                'hull' => $daedalus->getHull(),
                'shield' => $daedalus->getShield(),
                'createdAt' => $daedalus->getCreatedAt(),
                'updatedAt' => $daedalus->getUpdatedAt()
            ];
    }
}