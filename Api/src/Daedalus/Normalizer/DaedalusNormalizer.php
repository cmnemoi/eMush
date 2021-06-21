<?php

namespace Mush\Daedalus\Normalizer;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class DaedalusNormalizer implements ContextAwareNormalizerInterface
{
    private CycleServiceInterface $cycleService;
    private TranslationServiceInterface $translationService;

    public function __construct(
        CycleServiceInterface $cycleService,
        TranslationServiceInterface $translationService
    ) {
        $this->cycleService = $cycleService;
        $this->translationService = $translationService;
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
        $oxygenQuantity = $object->getOxygen();
        $fuelQuantity = $object->getFuel();
        $hullQuantity = $object->getHull();
        $shieldQuantity = $object->getShield();
        $cryoPlayer = $gameConfig->getCharactersConfig()->count() - $daedalus->getPlayers()->count();
        $humanDead = $daedalus->getPlayers()->getHumanPlayer()->getPlayerDead()->count();
        $mushAlive = $daedalus->getPlayers()->getMushPlayer()->getPlayerAlive()->count();
        $mushDead = $daedalus->getPlayers()->getMushPlayer()->getPlayerDead()->count();

        return [
                'id' => $object->getId(),
                'game_config' => $object->getGameConfig()->getId(),
                'cycle' => $object->getCycle(),
                'day' => $object->getDay(),
                'oxygen' => [
                    'quantity' => $oxygenQuantity,
                    'name' => $this->translationService->translate('oxygen.name', ['maximum' => $gameConfig->getDaedalusConfig()->getMaxOxygen(), 'quantity' => $oxygenQuantity], 'daedalus'),
                    'description' => $this->translationService->translate('oxygen.description', [], 'daedalus'), ],
                'fuel' => [
                    'quantity' => $fuelQuantity,
                    'name' => $this->translationService->translate('fuel.name', ['maximum' => $gameConfig->getDaedalusConfig()->getMaxFuel(), 'quantity' => $fuelQuantity], 'daedalus'),
                    'description' => $this->translationService->translate('fuel.description', [], 'daedalus'), ],
                'hull' => [
                    'quantity' => $hullQuantity,
                    'name' => $this->translationService->translate('hull.name', ['maximum' => $gameConfig->getDaedalusConfig()->getMaxHull(), 'quantity' => $hullQuantity], 'daedalus'),
                    'description' => $this->translationService->translate('hull.description', [], 'daedalus'), ],
                'shield' => [
                    'quantity' => $shieldQuantity,
                    'name' => $this->translationService->translate('shield.name', ['quantity' => $shieldQuantity], 'daedalus'),
                    'description' => $this->translationService->translate('shield.description', [], 'daedalus'), ],
                'nextCycle' => $this->cycleService->getDateStartNextCycle($object)->format(\DateTime::ATOM),
                'currentCycle' => [
                    'name' => $this->translationService->translate('currentCycle.name', [], 'daedalus'),
                    'description' => $this->translationService->translate('currentCycle.description', [], 'daedalus'), ],
                'cryogenizedPlayers' => $cryoPlayer,
                'humanPlayerAlive' => $daedalus->getPlayers()->getHumanPlayer()->getPlayerAlive()->count(),
                'humanPlayerDead' => $humanDead,
                'mushPlayerAlive' => $mushAlive,
                'mushPlayerDead' => $mushDead,
                'calendar' => [
                    'name' => $this->translationService->translate('calendar.name', [], 'daedalus'),
                    'description' => $this->translationService->translate('calendar.description', [], 'daedalus'), ],
                'crewPlayer' => [
                    'name' => $this->translationService->translate('crewPlayer.name', [], 'daedalus'),
                    'description' => $this->translationService->translate('crewPlayer.description',
                    ['cryogenizedPlayers' => $cryoPlayer,
                        'playerAlive' => $daedalus->getPlayers()->getPlayerAlive()->count(),
                        'humanDead' => $humanDead,
                        'mushAlive' => $mushAlive,
                        'mushDead' => $mushDead,
                    ], 'daedalus'), ],
            ];
    }
}
