<?php

namespace Mush\Status\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Criteria\StatusCriteria;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\StatusEvent;
use Mush\Status\Repository\StatusRepository;

class StatusService implements StatusServiceInterface
{
    private EntityManagerInterface $entityManager;
    private StatusRepository $statusRepository;
    private EventServiceInterface $eventService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        StatusRepository $statusRepository,
    ) {
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
        $this->statusRepository = $statusRepository;
    }

    public function persist(Status $status): Status
    {
        $this->entityManager->persist($status);
        $this->entityManager->flush();

        return $status;
    }

    public function delete(Status $status): bool
    {
        $status->getOwner()->removeStatus($status);

        $this->entityManager->remove($status);
        $this->entityManager->flush();

        return true;
    }

    public function removeAllStatuses(StatusHolderInterface $holder, array $reasons, \DateTime $time): void
    {
        /** @var Status $status */
        foreach ($holder->getStatuses() as $status) {
            $this->removeStatus($status->getName(), $holder, $reasons, $time);
        }
    }

    public function removeStatus(string $statusName, StatusHolderInterface $holder, array $reasons, \DateTime $time): void
    {
        $statusEvent = new StatusEvent(
            $statusName,
            $holder,
            $reasons,
            $time
        );
        $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_REMOVED);
    }

    public function getStatusConfigByNameAndDaedalus(string $name, Daedalus $daedalus): StatusConfig
    {
        $statusConfigs = $daedalus->getGameConfig()->getStatusConfigs()->filter(fn (StatusConfig $statusConfig) => $statusConfig->getStatusName() === $name);

        if ($statusConfigs->count() < 1) {
            throw new \LogicException("there should be at least 1 statusConfig with this name ({$name}). There are currently {$statusConfigs->count()}");
        }

        return $statusConfigs->first();
    }

    public function createStatusFromConfig(
        StatusConfig $statusConfig,
        StatusHolderInterface $holder,
        array $reasons,
        \DateTime $time,
        ?StatusHolderInterface $target = null
    ): Status {
        if ($statusConfig instanceof ChargeStatusConfig) {
            $status = new ChargeStatus($holder, $statusConfig);
        } else {
            $status = new Status($holder, $statusConfig);
        }
        $status->setTarget($target);

        $this->persist($status);

        $statusEvent = new StatusEvent(
            $statusConfig->getStatusName(),
            $holder,
            $reasons,
            $time
        );
        $statusEvent->setStatusConfig($statusConfig);
        $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);

        return $status;
    }

    public function createStatusFromName(
        string $statusName,
        Daedalus $daedalus,
        StatusHolderInterface $holder,
        \DateTime $time,
        ?StatusHolderInterface $target = null
    ): Status {
        $statusConfig = $this->getStatusConfigByNameAndDaedalus($statusName, $daedalus);

        if ($statusConfig instanceof ChargeStatusConfig) {
            $status = new ChargeStatus($holder, $statusConfig);
        } else {
            $status = new Status($holder, $statusConfig);
        }
        $status->setTarget($target);

        return $this->persist($status);
    }

    private function createAttemptStatus(string $action, Player $player): Attempt
    {
        /** @var ChargeStatusConfig $attemptConfig */
        $attemptConfig = $this->getStatusConfigByNameAndDaedalus(StatusEnum::ATTEMPT, $player->getDaedalus());

        $attempt = new Attempt($player, $attemptConfig);
        $attempt->setAction($action);

        return $attempt;
    }

    public function handleAttempt(Player $player, string $actionName, ActionResult $result): void
    {
        /** @var Attempt $attempt */
        $attempt = $player->getStatusByName(StatusEnum::ATTEMPT);

        if ($result instanceof Success) {
            $this->handleAttemptOnSuccess($attempt);
        } else {
            $this->handleAttemptOnFailure($attempt, $player, $actionName);
        }
    }

    public function handleAttemptOnFailure(?Attempt $attempt, Player $player, string $actionName): void
    {
        if ($attempt && $attempt->getAction() !== $actionName) {
            // Re-initialize attempts with new action
            $attempt
                ->setAction($actionName)
                ->setCharge(0)
            ;
        } elseif ($attempt === null) { // Create Attempt
            $attempt = $this->createAttemptStatus(
                $actionName,
                $player
            );
        }
        $attempt->addCharge(1);
        $this->persist($attempt);
    }

    public function handleAttemptOnSuccess(?Attempt $attempt): void
    {
        if ($attempt !== null) {
            $this->delete($attempt);
        }
    }

    public function getMostRecent(string $statusName, Collection $equipments): gameEquipment
    {
        $pickedEquipments = $equipments
            ->filter(fn (GameEquipment $gameEquipment) => $gameEquipment->getStatusByName($statusName) !== null)
        ;
        if ($pickedEquipments->isEmpty()) {
            throw new \Exception("no such status ({$statusName}) in item collection");
        } else {
            /** @var GameEquipment $pickedEquipment */
            $pickedEquipment = $pickedEquipments->first();
            if ($pickedEquipments->count() > 1) {
                /** @var GameEquipment $equipment */
                foreach ($pickedEquipments as $equipment) {
                    $pickedEquipmentsStatus = $pickedEquipment->getStatusByName($statusName);
                    $equipmentsStatus = $equipment->getStatusByName($statusName);
                    if ($pickedEquipmentsStatus &&
                        $equipmentsStatus &&
                        $pickedEquipmentsStatus->getCreatedAt() < $equipmentsStatus->getCreatedAt()) {
                        $pickedEquipment = $equipment;
                    }
                }
            }
        }

        return $pickedEquipment;
    }

    public function getByCriteria(StatusCriteria $criteria): Collection
    {
        return new ArrayCollection($this->statusRepository->findByCriteria($criteria));
    }

    public function getByTargetAndName(StatusHolderInterface $target, string $name): ?Status
    {
        return $this->statusRepository->findByTargetAndName($target, $name);
    }

    public function updateCharge(ChargeStatus $chargeStatus, int $delta, array $tags, \DateTime $time): ?ChargeStatus
    {
        $newCharge = $chargeStatus->getCharge() + $delta;
        $threshold = $chargeStatus->getThreshold();

        if ($chargeStatus->isAutoRemove() && ($newCharge >= $threshold || $newCharge <= 0)) {
            $this->removeStatus($chargeStatus->getName(), $chargeStatus->getOwner(), $tags, $time);

            return null;
        }

        if ($threshold) {
            $chargeStatus->setCharge(max(min($newCharge, $threshold), 0));
        } else {
            $chargeStatus->setCharge(max($newCharge, 0));
        }

        $this->persist($chargeStatus);

        return $chargeStatus;
    }
}
