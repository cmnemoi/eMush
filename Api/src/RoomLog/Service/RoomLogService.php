<?php

namespace Mush\RoomLog\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Entity\Target;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogDeclinationEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Repository\RoomLogRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

class RoomLogService implements RoomLogServiceInterface
{
    private EntityManagerInterface $entityManager;
    private RandomServiceInterface $randomService;
    private RoomLogRepository $repository;
    private TranslatorInterface $translator;

    public function __construct(
        EntityManagerInterface $entityManager,
        RandomServiceInterface $randomService,
        RoomLogRepository $repository,
        TranslatorInterface $translator
    ) {
        $this->entityManager = $entityManager;
        $this->randomService = $randomService;
        $this->repository = $repository;
        $this->translator = $translator;
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

    public function createLogFromActionResult(string $actionName, ActionResult $actionResult, Player $player): ?RoomLog
    {
        $logMapping = ActionLogEnum::ACTION_LOGS[$actionName] ?? null;

        if (!$logMapping) {
            return null;
        }

        $logData = null;
        if ($actionResult instanceof Success && isset($logMapping[ActionLogEnum::SUCCESS])) {
            $logData = $logMapping[ActionLogEnum::SUCCESS];
        } elseif ($actionResult instanceof Fail && isset($logMapping[ActionLogEnum::FAIL])) {
            $logData = $logMapping[ActionLogEnum::FAIL];
        } else {
            return $this->createActionLog(
                'no_log_yet_' . $actionName,
                $player->getRoom(),
                $player,
                null,
                VisibilityEnum::PUBLIC
            );
        }

        return $this->createActionLog(
            $logData[ActionLogEnum::VALUE],
            $player->getRoom(),
            $player,
            $actionResult->getTarget(),
            $logData[ActionLogEnum::VISIBILITY]
        );
    }

    private function createLog(
        string $logKey,
        Room $room,
        ?Player $player,
        ?Target $target,
        ?int $quantity,
        string $visibility,
        string $type,
        \DateTime $dateTime = null
    ): RoomLog {
        if ($declinations = LogDeclinationEnum::getDeclination($logKey)) {
            $logKey = $this->randomService->getSingleRandomElementFromProbaArray($declinations);
        }

        $roomLog = new RoomLog();
        $roomLog
            ->setLog($logKey)
            ->setType($type)
            ->setPlayer($player)
            ->setTarget($target)
            ->setRoom($room)
            ->setVisibility($visibility)
            ->setDate($dateTime ?? new \DateTime('now'))
            ->setQuantity($quantity)
            ->setCycle($room->getDaedalus()->getCycle())
            ->setDay($room->getDaedalus()->getDay())
        ;

        return $roomLog;
    }

    public function createActionLog(
        string $logKey,
        Room $room,
        Player $player,
        ?Target $target,
        string $visibility,
        \DateTime $dateTime = null
    ): RoomLog {
        $log = $this->createLog($logKey, $room, $player, $target, null, $visibility, 'actions_log');

        $this->persist($log);

        return $log;
    }

    public function createPlayerLog(
        string $logKey,
        Room $room,
        Player $player,
        string $visibility,
        \DateTime $dateTime = null
    ): RoomLog {
        return $this->persist(
            $this->createLog($logKey, $room, $player, null, null, $visibility, 'event_log', $dateTime)
        );
    }

    public function createQuantityLog(
        string $logKey,
        Room $room,
        Player $player,
        string $visibility,
        int $quantity,
        \DateTime $dateTime = null
    ): RoomLog {
        return $this->persist(
            $this->createLog($logKey, $room, $player, null, $quantity, $visibility, 'event_log', $dateTime)
        );
    }

    public function createEquipmentLog(
        string $logKey,
        Room $room,
        ?Player $player,
        GameEquipment $gameEquipment,
        string $visibility,
        \DateTime $dateTime = null
    ): RoomLog {
        $type = $gameEquipment instanceof GameItem ? 'items' : 'equipments';
        $target = new Target($gameEquipment->getName(), $type);

        return $this->persist(
            $this->createLog($logKey, $room, $player, $target, null, $visibility, 'event_log', $dateTime)
        );
    }

    public function createRoomLog(
        string $logKey,
        Room $room,
        string $visibility,
        \DateTime $dateTime = null
    ): RoomLog {
        return $this->persist(
            $this->createLog($logKey, $room, null, null, null, $visibility, 'event_log', $dateTime)
        );
    }

    public function getRoomLog(Player $player): array
    {
        $roomLogs = $this->repository->getPlayerRoomLog($player);

        $logs = [];
        /** @var RoomLog $roomLog */
        foreach ($roomLogs as $roomLog) {
            $logKey = $roomLog->getLog();
            $params = [];
            if ($player = $roomLog->getPlayer()) {
                $characterKey = $player->getCharacterConfig()->getName();
                $characterName = $this->translator->trans($characterKey . '.name', [], 'characters');
                $logKey .= '.character.' . (CharacterEnum::isMale($characterKey) ? 'male' : 'female');
                $params['player'] = $characterName;
            }

            if ($target = $roomLog->getTarget()) {
                $targetName = $this->translator->trans($target->getName() . '.short_name', [], $target->getType());
                $targetGenre = $this->translator->trans($target->getName() . '.genre', [], $target->getType());

                $logKey .= '.target.' . $targetGenre;
                $params['target'] = $targetName;
            }

            if ($roomLog->getQuantity() !== null) {
                $params['quantity'] = $roomLog->getQuantity();
            }

            $logs[$roomLog->getDay()][$roomLog->getCycle()][] = [
                'log' => $this->translator->trans(
                    $logKey,
                    $params,
                    $roomLog->getType()
                ),
                'visibility' => $roomLog->getVisibility(),
                'date' => $roomLog->getDate(),
            ];
        }

        return $logs;
    }
}
