<?php

namespace Mush\Daedalus\Normalizer;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\CycleServiceInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Mush\Game\Service\TranslationServiceInterface;

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

        return [
                'id' => $object->getId(),
                'game_config' => $object->getGameConfig()->getId(),
                'cycle' => $object->getCycle(),
                'day' => $object->getDay(),
                'oxygen' => [
                    'quantity' => $object ->getOxygen(),
                    'name' => $this->translationService->translate('oxygen.name', [], 'daedalus'),
                    'description' => $this->translationService->translate('oxygen.description', [], 'daedalus')],
                'fuel' => [
                    'quantity' => $object->getFuel(),
                    'name' => $this->translationService->translate('fuel.name', [], 'daedalus'),
                    'description' => $this->translationService->translate('fuel.description', [], 'daedalus')],
                'hull' => [
                    'quantity' => $object->getHull(),
                    'name' => $this->translationService->translate('hull.name', [], 'daedalus'),
                    'description' => $this->translationService->translate('hull.description', [], 'daedalus')],
                'shield' => [
                    'quantity' => $object->getShield(),
                    'name' => $this->translationService->translate('shield.name', [], 'daedalus'),
                    'description' => $this->translationService->translate('shield.description', [], 'daedalus')],
                'nextCycle' => $this->cycleService->getDateStartNextCycle($object)->format(\DateTime::ATOM),
                'currentCycle' => [
                    'name' => $this->translationService->translate('currentCycle.name', [], 'daedalus'),
                    'description' => $this->translationService->translate('currentCycle.description', [], 'daedalus')],
                'cryogenizedPlayers' => $gameConfig->getCharactersConfig()->count() - $daedalus->getPlayers()->count(),
                'humanPlayerAlive' => [
                    'quantity' => $daedalus->getPlayers()->getHumanPlayer()->getPlayerAlive()->count(),
                    'name' => $this->translationService->translate('humanPlayerAlive.name', [], 'daedalus'),
                    'description' => $this->translationService->translate('humanPlayerAlive.description', [], 'daedalus')],
                'humanPlayerDead' => $daedalus->getPlayers()->getHumanPlayer()->getPlayerDead()->count(),
                'mushPlayerAlive' => $daedalus->getPlayers()->getMushPlayer()->getPlayerAlive()->count(),
                'mushPlayerDead' => $daedalus->getPlayers()->getMushPlayer()->getPlayerDead()->count(),
            ];
    }
}
