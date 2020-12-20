<?php

namespace Mush\RoomLog\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Entity\Target;
use Mush\RoomLog\Enum\LogDeclinationEnum;
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

    private function createLog(
        string $logKey,
        Room $room,
        ?Player $player,
        ?Target $target,
        ?int $quantity,
        string $visibility,
        \DateTime $dateTime = null
    ): RoomLog {
        if ($declinations = LogDeclinationEnum::getDeclination($logKey)) {
            $logKey = $this->randomService->getSingleRandomElementFromProbaArray($declinations);
        }

        $roomLog = new RoomLog();
        $roomLog
            ->setLog($logKey)
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

    public function createPlayerLog(
        string $logKey,
        Room $room,
        Player $player,
        string $visibility,
        \DateTime $dateTime = null
    ): RoomLog {
        return $this->persist($this->createLog($logKey, $room, $player, null, null, $visibility, $dateTime));
    }

    public function createQuantityLog(
        string $logKey,
        Room $room,
        Player $player,
        string $visibility,
        int $quantity,
        \DateTime $dateTime = null
    ): RoomLog {
        return $this->persist($this->createLog($logKey, $room, $player, null, $quantity, $visibility, $dateTime));
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

        return $this->persist($this->createLog($logKey, $room, $player, $target, null, $visibility, $dateTime));
    }

    public function createRoomLog(
        string $logKey,
        Room $room,
        string $visibility,
        \DateTime $dateTime = null
    ): RoomLog {
        return $this->persist($this->createLog($logKey, $room, null, null, null, $visibility, $dateTime));
    }

    public function getRoomLog(Player $player): array
    {
        $roomLogs = $this->repository->getPlayerRoomLog($player);

        $logs = [];
        /** @var RoomLog $roomLog */
        foreach ($roomLogs as $roomLog) {
            $logKey = $roomLog->getLog();
            $params = [];
            if ($roomLog->getPlayer()) {
                $characterKey = $roomLog->getPlayer()->getPerson();
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
                    'log'
                ),
                'visibility' => $roomLog->getVisibility(),
                'date' => $roomLog->getDate(),
            ];
        }

        return $logs;
    }
}
