<?php

namespace Mush\RoomLog\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Equipment\Entity\Door;
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
use Symfony\Contracts\Translation\TranslatorInterface;

class RoomLogService implements RoomLogServiceInterface
{
    private EntityManagerInterface $entityManager;
    private RandomServiceInterface $randomService;
    private RoomLogRepository $repository;
    private TranslatorInterface $translator;
    private TranslationServiceInterface $translationService;

    public function __construct(
        EntityManagerInterface $entityManager,
        RandomServiceInterface $randomService,
        RoomLogRepository $repository,
        TranslatorInterface $translator,
        TranslationServiceInterface $translationService,
    ) {
        $this->entityManager = $entityManager;
        $this->randomService = $randomService;
        $this->repository = $repository;
        $this->translator = $translator;
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
            return $this->createLog(
                'no_log_yet_' . $actionName,
                $player->getPlace(),
                VisibilityEnum::PUBLIC,
                'actions_log',
                $player,
            );
        }

        return $this->createLog(
            $logData[ActionLogEnum::VALUE],
            $player->getPlace(),
            $logData[ActionLogEnum::VISIBILITY],
            'actions_log',
            $player,
            $actionResult->getTargetPlayer() ?? $actionResult->getTargetEquipment(),
            $actionResult->getQuantity()
        );
    }

    public function createLog(
        string $logKey,
        Place $place,
        string $visibility,
        string $type,
        ?Player $player = null,
        ?LogParameter $target = null,
        ?int $quantity = null,
        \DateTime $dateTime = null
    ): RoomLog {
        $params = $this->getMessageParam($player, $target, $quantity);

        //if there is several version of the log
        if (array_key_exists($logKey, $declinations = LogDeclinationEnum::getVersionNumber())) {
            foreach ($declinations[$logKey] as $keyVersion => $versionNb) {
                $params[$keyVersion] = $this->randomService->random(1, $versionNb);
            }
        }

        $roomLog = new RoomLog();
        $roomLog
            ->setLog($logKey)
            ->setParameters($params)
            ->setType($type)
            ->setPlace($place)
            ->setPlayer($player)
            ->setVisibility($visibility)
            ->setDate($dateTime ?? new \DateTime('now'))
            ->setCycle($place->getDaedalus()->getCycle())
            ->setDay($place->getDaedalus()->getDay())

        ;

        return $this->persist($roomLog);
    }

    private function getMessageParam(
        ?Player $player = null,
        ?LogParameter $target = null,
        ?int $quantity = null
    ): array {
        $params = [];

        if ($player !== null) {
            $params['player'] = $player->getCharacterConfig()->getName();
        }

        if ($target instanceof GameEquipment) {
            if ($target instanceof GameItem) {
                $params['targetItem'] = $target->getName();
            } elseif ($target instanceof Door) {
                $params['targetEquipment'] = EquipmentEnum::DOOR;
            } else {
                $params['targetEquipment'] = $target->getName();
            }
        }

        if ($target instanceof Player) {
            $params['targetPlayer'] = $target->getCharacterConfig()->getName();
        }

        if ($quantity !== null) {
            $params['quantity'] = $quantity;
        }

        return $params;
    }

    public function getRoomLog(Player $player): array
    {
        $roomLogs = $this->repository->getPlayerRoomLog($player);

        $logs = [];
        /** @var RoomLog $roomLog */
        foreach ($roomLogs as $roomLog) {
            $translatedParameters = $this->translationService->getTranslateParameters($roomLog->getParameters());
            $logs[$roomLog->getDay()][$roomLog->getCycle()][] = [
                'log' => $this->translator->trans(
                    $roomLog->getLog(),
                    $translatedParameters,
                    $roomLog->getType()
                ),
                'visibility' => $roomLog->getVisibility(),
                'date' => $roomLog->getDate(),
            ];
        }

        return $logs;
    }
}
