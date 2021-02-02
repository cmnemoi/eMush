<?php

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\AlertEnum;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Status\Criteria\StatusCriteria;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DaedalusAlertsService implements DaedalusAlertsServiceInterface
{
    public const OXYGEN_ALERT = 8;
    public const HULL_ALERT = 33;

    private TranslatorInterface $translator;
    private StatusServiceInterface $statusService;

    public function __construct(TranslatorInterface $translator, StatusServiceInterface $statusService)
    {
        $this->translator = $translator;
        $this->statusService = $statusService;
    }

    public function getAlerts(Daedalus $daedalus): array
    {
        $alerts = [];

        $numberAlert = array_filter($this->countAlert($daedalus), function (int $value) {return $value > 0; });

        foreach ($numberAlert as $key => $number) {
            $alerts[$key] = $this->translateAlert($key, $number);
        }

        if ($daedalus->getOxygen() < self::OXYGEN_ALERT) {
            $alerts[AlertEnum::LOW_OXYGEN] = $this->translateAlert(AlertEnum::LOW_OXYGEN);
        }
        if ($daedalus->getHull() <= self::HULL_ALERT) {
            $alerts[AlertEnum::LOW_HULL] = $this->translateAlert(AlertEnum::LOW_HULL, $daedalus->getHull());
        }

        if (count($alerts) === 0) {
            $alerts[AlertEnum::NO_ALERT] = $this->translateAlert(AlertEnum::NO_ALERT);
        }

        return $alerts;
    }

    private function countAlert(Daedalus $daedalus): array
    {
        $fire = 0;
        $brokenDoors = 0;
        $brokenEquipments = 0;

        $criteria = new StatusCriteria($daedalus);
        $criteria->setName([StatusEnum::FIRE, EquipmentStatusEnum::BROKEN]);

        $alertStatuses = $this->statusService->getByCriteria($criteria);

        /** @var Status $status */
        foreach ($alertStatuses as $status) {
            switch ($status->getName()) {
                case StatusEnum::FIRE:
                    $fire++;
                    break;
                case EquipmentStatusEnum::BROKEN:
                    if ($status->getOwner()->getClassName() === Door::class) {
                        ++$brokenDoors;
                    } elseif ($status->getOwner()->getClassName() === GameEquipment::class) {
                        ++$brokenEquipments;
                    }
                    break;
            }
        }

        return [AlertEnum::NUMBER_FIRE => $fire, AlertEnum::BROKEN_DOORS => $brokenDoors, AlertEnum::BROKEN_EQUIPMENTS => $brokenEquipments];
    }

    public function translateAlert(string $key, ?int $quantity = null): array
    {
        if ($quantity !== null) {
            if ($quantity > 1) {
                $plural = '.plural';
            } else {
                $plural = '.single';
            }
            $alert = [
                'name' => $this->translator->trans($key . '.name' . $plural, ['quantity' => $quantity], 'alerts'),
                'description' => $this->translator->trans($key . '.description', [], 'alerts'),
            ];
        } else {
            $alert = [
                'name' => $this->translator->trans($key . '.name', [], 'alerts'),
                'description' => $this->translator->trans($key . '.description', [], 'alerts'),
            ];
        }

        return $alert;
    }
}
