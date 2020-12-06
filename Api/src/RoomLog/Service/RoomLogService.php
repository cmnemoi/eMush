<?php

namespace Mush\RoomLog\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Entity\Target;
use Mush\RoomLog\Repository\RoomLogRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

class RoomLogService implements RoomLogServiceInterface
{
    private EntityManagerInterface $entityManager;
    private RoomLogRepository $repository;
    private TranslatorInterface $translator;

    public function __construct(
        EntityManagerInterface $entityManager,
        RoomLogRepository $repository,
        TranslatorInterface $translator
    ) {
        $this->entityManager = $entityManager;
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

    public function createPlayerLog(
        string $logKey,
        Room $room,
        Player $player,
        string $visibility,
        \DateTime $dateTime = null
    ): RoomLog {
        $roomLog = new RoomLog();
        $roomLog
            ->setLog($logKey)
            ->setPlayer($player)
            ->setRoom($room)
            ->setVisibility($visibility)
            ->setDate($dateTime ?? new \DateTime('now'))
            ->setCycle($room->getDaedalus()->getCycle())
            ->setDay($room->getDaedalus()->getDay())
        ;

        return $this->persist($roomLog);
    }

    public function createQuantityLog(
        string $logKey,
        Room $room,
        Player $player,
        string $visibility,
        int $quantity,
        \DateTime $dateTime = null
    ): RoomLog {
        $roomLog = new RoomLog();
        $roomLog
            ->setLog($logKey)
            ->setPlayer($player)
            ->setRoom($room)
            ->setVisibility($visibility)
            ->setQuantity($quantity)
            ->setDate($dateTime ?? new \DateTime('now'))
            ->setCycle($room->getDaedalus()->getCycle())
            ->setDay($room->getDaedalus()->getDay())
        ;

        return $this->persist($roomLog);
    }

    public function createEquipmentLog(
        string $logKey,
        Room $room,
        ?Player $player,
        GameEquipment $equipment,
        string $visibility,
        \DateTime $dateTime = null
    ): RoomLog {
        $roomLog = new RoomLog();
        $roomLog
            ->setLog($logKey)
            ->setTarget(new Target($equipment->getName(), 'equipments'))
            ->setPlayer($player)
            ->setRoom($room)
            ->setVisibility($visibility)
            ->setDate($dateTime ?? new \DateTime('now'))
            ->setCycle($room->getDaedalus()->getCycle())
            ->setDay($room->getDaedalus()->getDay())
        ;

        return $this->persist($roomLog);
    }

    public function createRoomLog(
        string $logKey,
        Room $room,
        string $visibility,
        \DateTime $dateTime = null
    ): RoomLog {
        $roomLog = new RoomLog();
        $roomLog
            ->setLog($logKey)
            ->setRoom($room)
            ->setVisibility($visibility)
            ->setDate($dateTime ?? new \DateTime('now'))
            ->setCycle($room->getDaedalus()->getCycle())
            ->setDay($room->getDaedalus()->getDay())
        ;

        return $this->persist($roomLog);
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
                'date' => $roomLog->getDate(),
            ];
        }

        return $logs;
    }
}
