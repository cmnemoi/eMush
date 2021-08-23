<?php

namespace Mush\RoomLog\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameter;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogDeclinationEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Repository\RoomLogRepository;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

class RoomLogService implements RoomLogServiceInterface
{
    private EntityManagerInterface $entityManager;
    private RandomServiceInterface $randomService;
    private RoomLogRepository $repository;
    private TranslationServiceInterface $translationService;

    public function __construct(
        EntityManagerInterface $entityManager,
        RandomServiceInterface $randomService,
        RoomLogRepository $repository,
        TranslationServiceInterface $translationService,
    ) {
        $this->entityManager = $entityManager;
        $this->randomService = $randomService;
        $this->repository = $repository;
        $this->translationService = $translationService;
    }

    public function persist(RoomLog $roomLog): RoomLog
    {
        $this->entityManager->persist($roomLog);
        $this->entityManager->flush();

        return $roomLog;
    }

    public function findById(int $id): ?RoomLog
    {
        return $this->repository->find($id);
    }

    public function createLogFromActionResult(
        string $actionName,
        ActionResult $actionResult,
        Player $player,
        ActionParameter $actionParameter,
    ): ?RoomLog
    {
        // first lets handle the special case of examine action
        if ($actionName === ActionEnum::EXAMINE && $actionParameter !== null) {
            if ($actionParameter instanceof GameItem) {
                $type = 'items';
            } else {
                $type = 'equipments';
            }

            return $this->createLog(
                $actionParameter->getLogName() . '.examine',
                $player->getPlace(),
                VisibilityEnum::PRIVATE,
                $type,
                $player,
            );
        }

        $logMapping = ActionLogEnum::ACTION_LOGS[$actionName] ?? null;

        if (!$logMapping) {
            return null;
        }

        if ($actionResult instanceof Success && isset($logMapping[ActionLogEnum::SUCCESS])) {
            $logData = $logMapping[ActionLogEnum::SUCCESS];
        } elseif ($actionResult instanceof Fail && isset($logMapping[ActionLogEnum::FAIL])) {
            $logData = $logMapping[ActionLogEnum::FAIL];
        } else {
            return $this->createLog(
                'no_log_yet_' . $actionName,
                $player->getPlace(),
                VisibilityEnum::PUBLIC,
                'actions_log',
                $player,
            );
        }

        $parameters = [];
        if (($quantity = $actionResult->getQuantity()) !== null) {
            $parameters['quantity'] = $quantity;
        }
        if ($actionParameter !== null){
            if (!$actionParameter instanceof LogParameter){
                throw new InvalidTypeException($actionParameter, LogParameter::class);
            }

            $key = 'target_'.$actionParameter->getLogKey();
            $parameters[$key] = $actionParameter->getLogName();
        }
        if (($equipment = $actionResult->getEquipment()) !== null){
            $parameters[$equipment->getLogKey()] = $equipment->getLogName();
        }

        return $this->createLog(
            $logData[ActionLogEnum::VALUE],
            $player->getPlace(),
            $logData[ActionLogEnum::VISIBILITY],
            'actions_log',
            $player,
            $parameters,
        );
    }

    public function createLog(
        string $logKey,
        Place $place,
        string $visibility,
        string $type,
        ?Player $player = null,
        array $parameters = [],
        \DateTime $dateTime = null
    ): RoomLog {
        //if there is several version of the log
        if (array_key_exists($logKey, $declinations = LogDeclinationEnum::getVersionNumber())) {
            foreach ($declinations[$logKey] as $keyVersion => $versionNb) {
                $parameters[$keyVersion] = $this->randomService->random(1, $versionNb);
            }
        }
        if ($player !== null){
            $parameters[$player->getLogKey()] = $player->getLogName();
        }

        $roomLog = new RoomLog();
        $roomLog
            ->setLog($logKey)
            ->setParameters($parameters)
            ->setType($type)
            ->setPlace($place)
            ->setPlayer($player)
            ->setVisibility($this->getVisibility($player, $visibility))
            ->setDate($dateTime ?? new \DateTime('now'))
            ->setCycle($place->getDaedalus()->getCycle())
            ->setDay($place->getDaedalus()->getDay())

        ;

        return $this->persist($roomLog);
    }

    private function getVisibility(?Player $player, string $visibility): string
    {
        if ($player === null) {
            return $visibility;
        }

        $place = $player->getPlace();
        if (
            $visibility === VisibilityEnum::COVERT &&
            ($place->getPlayers()->count() > 1 ||
            !$place->getEquipments()
                ->filter(fn (GameEquipment $gameEquipment) => (
                    $gameEquipment->getName() === EquipmentEnum::CAMERA_EQUIPMENT
                ))->isEmpty())
        ) {
            return VisibilityEnum::REVEALED;
        } elseif (
            $visibility === VisibilityEnum::SECRET &&
            !$place->getEquipments()
                ->filter(fn (GameEquipment $gameEquipment) => (
                    $gameEquipment->getName() === EquipmentEnum::CAMERA_EQUIPMENT
                ))->isEmpty()
        ) {
            return VisibilityEnum::REVEALED;
        }

        return $visibility;
    }


    public function getRoomLog(Player $player): array
    {
        $roomLogs = $this->repository->getPlayerRoomLog($player);

        $logs = [];
        /** @var RoomLog $roomLog */
        foreach ($roomLogs as $roomLog) {
            $logs[$roomLog->getDay()][$roomLog->getCycle()][] = [
                'log' => $this->translationService->translate(
                    $roomLog->getLog(),
                    $roomLog->getParameters(),
                    $roomLog->getType()
                ),
                'visibility' => $roomLog->getVisibility(),
                'date' => $roomLog->getDate(),
            ];
        }

        return $logs;
    }
}
