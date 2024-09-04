<?php

namespace Mush\Alert\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Enum\AlertEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AlertNormalizer implements NormalizerInterface
{
    private TranslationServiceInterface $translationService;

    public function __construct(
        TranslationServiceInterface $translationService,
    ) {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Alert;
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        /** @var Alert $alert */
        $alert = $object;

        $daedalus = $alert->getDaedalus();
        $language = $daedalus->getLanguage();

        $key = $alert->getName();

        $normalizedAlert = [
            'prefix' => $this->translationService->translate(
                'alerts',
                [],
                'alerts',
                $language
            ),
            'key' => $key,
        ];

        if (($quantity = $this->getAlertQuantity($alert)) !== null) {
            $normalizedAlert['name'] = $this->translationService->translate(
                $alert->getName() . '.name',
                ['quantity' => $quantity],
                'alerts',
                $language
            );
            $normalizedAlert['description'] = $this->translationService->translate(
                "{$key}.description",
                ['quantity' => $quantity],
                'alerts',
                $language
            );
        } else {
            $normalizedAlert['name'] = $this->translationService->translate(
                $alert->getName() .
                '.name',
                [],
                'alerts',
                $language
            );
            $normalizedAlert['description'] = $this->translationService->translate(
                "{$key}.description",
                $this->getAlertDesriptionParameters($alert),
                'alerts',
                $language
            );
        }

        if (!$alert->getAlertElements()->isEmpty()) {
            $normalizedAlert['reports'] = $this->handleAlertReport($alert, $language);
        }

        if ($alert->getName() === AlertEnum::LOST_CREWMATE) {
            /** @var Player $lastLostPlayer */
            $lastLostPlayer = $alert->getDaedalus()->getLostPlayers()->last();
            $normalizedAlert['lostPlayer'] = $lastLostPlayer->getName();
        }

        return $normalizedAlert;
    }

    private function getAlertDesriptionParameters(Alert $alert): array
    {
        if ($alert->getName() === AlertEnum::OUTCAST) {
            return [$alert->getDaedalus()->getCurrentPariah()->getLogKey() => $alert->getDaedalus()->getCurrentPariah()->getLogName()];
        }

        return [];
    }

    private function getAlertQuantity(Alert $alert): ?int
    {
        if (!$alert->getAlertElements()->isEmpty()) {
            return $alert->getAlertElements()->count();
        }

        if ($alert->getName() === AlertEnum::LOW_HULL) {
            return $alert->getDaedalus()->getHull();
        }

        if ($alert->getName() === AlertEnum::HUNTER) {
            return $alert->getDaedalus()->getAttackingHunters()->count();
        }

        return null;
    }

    private function handleAlertReport(Alert $alert, string $language): array
    {
        if (
            $alert->getName() === AlertEnum::BROKEN_EQUIPMENTS
            || $alert->getName() === AlertEnum::BROKEN_DOORS
        ) {
            return $this->handleBrokenEquipmentsAlertsReports($alert, $language);
        }

        $reports = [];

        /** @var AlertElement $element */
        foreach ($alert->getAlertElements() as $element) {
            $playerInfo = $element->getPlayerInfo();

            if ($playerInfo !== null) {
                /** @var Place $place */
                $place = $element->getPlace();

                $placeName = $this->translationService->translate(
                    $place->getName(),
                    [],
                    'rooms',
                    $language
                );
                $loc_prep = $this->translationService->translate(
                    $place->getName() . '.loc_prep',
                    [],
                    'rooms',
                    $language
                );
                $parameters = [
                    'character' => $playerInfo->getName(),
                    'place' => $placeName,
                    'loc_prep' => $loc_prep,
                ];

                $reports[] = $this->translationService->translate(
                    "{$alert->getName()}.report",
                    $parameters,
                    'alerts',
                    $language
                );
            }
        }

        return $reports;
    }

    /**
     * Special treatment for broken doors and equipment alert elements.
     * This will allow to display it like this :
     * - Chun has reported 3 broken doors on the bridge
     * - Derek has reported 1 broken equipment in the laboratory.
     *
     * @psalm-suppress PossiblyNullReference
     */
    private function handleBrokenEquipmentsAlertsReports(Alert $alert, string $language): array
    {
        $reportedAlertElements = $alert->getAlertElements()->filter(
            static fn (AlertElement $element) => $element->getPlayerInfo() !== null && $element->getPlace() !== null
        );

        $playerPlaceCount = [];

        /** @var AlertElement $element */
        foreach ($reportedAlertElements as $element) {
            $reporterName = $element->getPlayerInfo()->getName();
            $placeName = $element->getPlace()->getName();
            if (!isset($playerPlaceCount[$reporterName][$placeName])) {
                $playerPlaceCount[$reporterName][$placeName] = 0;
            }
            ++$playerPlaceCount[$reporterName][$placeName];
        }

        /** @var ArrayCollection<int, string> $normalizedReports */
        $normalizedReports = new ArrayCollection();

        /** @var AlertElement $element */
        foreach ($reportedAlertElements as $element) {
            $reporterName = $element->getPlayerInfo()->getName();
            $placeName = $element->getPlace()->getName();

            $locPrep = $this->translationService->translate(
                "{$placeName}.loc_prep",
                [],
                'rooms',
                $language
            );

            $normalizedReport = $this->translationService->translate(
                "{$alert->getName()}.report",
                [
                    'character' => $reporterName,
                    'place' => $placeName,
                    'loc_prep' => $locPrep,
                    'quantity' => $playerPlaceCount[$reporterName][$placeName],
                ],
                'alerts',
                $language
            );

            if ($normalizedReports->contains($normalizedReport)) {
                continue;
            }

            $normalizedReports->add($normalizedReport);
        }

        return $normalizedReports->toArray();
    }
}
