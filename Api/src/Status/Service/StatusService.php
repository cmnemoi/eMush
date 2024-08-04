<?php

namespace Mush\Status\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Status\Criteria\StatusCriteria;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\ContentStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\ContentStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\ChargeStatusEvent;
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
        if ($status->isNull()) {
            return $status;
        }

        $this->entityManager->persist($status);
        $this->entityManager->flush();

        return $status;
    }

    public function removeAllStatuses(StatusHolderInterface $holder, array $reasons, \DateTime $time): void
    {
        /** @var Status $status */
        foreach ($holder->getStatuses() as $status) {
            $this->removeStatus($status->getName(), $holder, $reasons, $time);
        }
    }

    public function removeStatus(
        string $statusName,
        StatusHolderInterface $holder,
        array $tags,
        \DateTime $time,
        string $visibility = VisibilityEnum::HIDDEN
    ): void {
        $status = $holder->getStatusByName($statusName);
        if ($status === null) {
            return;
        }

        $statusEvent = new StatusEvent(
            $status,
            $holder,
            $tags,
            $time,
            $status->getTarget()
        );
        $statusEvent->setVisibility($visibility);
        $events = $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_REMOVED);

        // If the event has been prevented, do not delete the event
        if ($events->getInitialEvent() === null) {
            return;
        }

        $this->delete($status);

        $statusEvent = new StatusEvent(
            $status,
            $holder,
            $tags,
            $time,
            $status->getTarget()
        );
        $statusEvent->setVisibility($visibility);
        $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_DELETED);
    }

    public function getStatusConfigByNameAndDaedalus(string $name, Daedalus $daedalus): StatusConfig
    {
        $statusConfigs = $daedalus->getGameConfig()->getStatusConfigs()->filter(static fn (StatusConfig $statusConfig) => $statusConfig->getStatusName() === $name);

        if ($statusConfigs->count() < 1) {
            throw new \LogicException("there should be at least 1 statusConfig with this name ({$name}). There are currently {$statusConfigs->count()}");
        }

        return $statusConfigs->first();
    }

    public function createStatusFromConfig(
        StatusConfig $statusConfig,
        StatusHolderInterface $holder,
        array $tags = [],
        \DateTime $time = new \DateTime(),
        ?StatusHolderInterface $target = null,
        string $visibility = VisibilityEnum::HIDDEN
    ): Status {
        if ($target !== null) {
            $status = $holder->getStatusByNameAndTarget($statusConfig->getStatusName(), $target);
        } else {
            $status = $holder->getStatusByName($statusConfig->getStatusName());
        }

        if ($status !== null) {
            return $status;
        }

        // Create the entity
        if ($statusConfig->isNull()) {
            $status = Status::createNull();
        } elseif ($statusConfig instanceof ChargeStatusConfig) {
            $status = new ChargeStatus($holder, $statusConfig);
        } elseif ($statusConfig instanceof ContentStatusConfig) {
            $status = new ContentStatus($holder, $statusConfig);
        } else {
            $status = new Status($holder, $statusConfig);
        }
        $status->setTarget($target);

        $this->persist($status);

        // Create and dispatch the event
        $statusEvent = new StatusEvent(
            $status,
            $holder,
            $tags,
            $time,
            $target
        );
        $statusEvent->setVisibility($visibility);

        // Check if the event is prevented by a modifier
        if ($this->eventService->computeEventModifications($statusEvent, StatusEvent::STATUS_APPLIED) === null) {
            $this->delete($status);
        }

        $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);

        return $status;
    }

    public function createStatusFromName(
        string $statusName,
        StatusHolderInterface $holder,
        array $tags,
        \DateTime $time,
        ?StatusHolderInterface $target = null,
        string $visibility = VisibilityEnum::HIDDEN
    ): Status {
        if ($target !== null) {
            $status = $holder->getStatusByNameAndTarget($statusName, $target);
        } else {
            $status = $holder->getStatusByName($statusName);
        }

        if ($status !== null) {
            return $status;
        }

        $statusConfig = $this->getStatusConfigByNameAndDaedalus($statusName, $holder->getDaedalus());

        return $this->createStatusFromConfig(
            $statusConfig,
            $holder,
            $tags,
            $time,
            $target,
            $visibility
        );
    }

    public function handleAttempt(
        StatusHolderInterface $holder,
        string $actionName,
        ActionResult $result,
        array $tags,
        \DateTime $time
    ): void {
        /** @var Attempt $attempt */
        $attempt = $holder->getStatusByName(StatusEnum::ATTEMPT);

        if ($result instanceof Success) {
            $this->handleAttemptOnSuccess($attempt);
        } else {
            $this->handleAttemptOnFailure($attempt, $holder, $actionName, $tags, $time);
        }
    }

    public function getMostRecent(string $statusName, Collection $equipments): GameEquipment
    {
        $pickedEquipments = $equipments
            ->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->getStatusByName($statusName) !== null);
        if ($pickedEquipments->isEmpty()) {
            throw new \Exception("no such status ({$statusName}) in item collection");
        }

        /** @var GameEquipment $pickedEquipment */
        $pickedEquipment = $pickedEquipments->first();
        if ($pickedEquipments->count() > 1) {
            /** @var GameEquipment $equipment */
            foreach ($pickedEquipments as $equipment) {
                $pickedEquipmentsStatus = $pickedEquipment->getStatusByName($statusName);
                $equipmentsStatus = $equipment->getStatusByName($statusName);
                if ($pickedEquipmentsStatus
                    && $equipmentsStatus
                    && $pickedEquipmentsStatus->getCreatedAt() < $equipmentsStatus->getCreatedAt()) {
                    $pickedEquipment = $equipment;
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

    public function updateCharge(
        ChargeStatus $chargeStatus,
        int $delta,
        array $tags,
        \DateTime $time,
        string $mode = VariableEventInterface::CHANGE_VARIABLE,
        string $visibility = VisibilityEnum::HIDDEN,
    ): ?ChargeStatus {
        $chargeVariable = $chargeStatus->getVariableByName($chargeStatus->getName());

        $statusEvent = new ChargeStatusEvent(
            $chargeStatus,
            $chargeStatus->getOwner(),
            $delta,
            $tags,
            $time,
        );
        $this->eventService->callEvent($statusEvent, $mode);

        if ($chargeStatus->isAutoRemove() && $chargeVariable->isMin()) {
            $this->removeStatus($chargeStatus->getName(), $chargeStatus->getOwner(), $tags, $time, $visibility);

            return null;
        }

        return $chargeStatus;
    }

    public function createOrIncrementChargeStatus(string $name, StatusHolderInterface $holder, string $visibility = VisibilityEnum::HIDDEN, ?StatusHolderInterface $target = null, array $tags = [], \DateTime $time = new \DateTime()): ChargeStatus
    {
        $chargeStatus = $holder->getStatusByName($name);
        if ($chargeStatus instanceof ChargeStatus) {
            /** @var ChargeStatus $chargeStatus */
            $chargeStatus = $this->updateCharge($chargeStatus, 1, $tags, $time);
        } else {
            /** @var ChargeStatus $chargeStatus */
            $chargeStatus = $this->createStatusFromName(
                $name,
                $holder,
                $tags,
                $time,
                $target,
                $visibility
            );
        }

        return $chargeStatus;
    }

    public function deleteAllStatusesByName(string $name): void
    {
        $statusesToDelete = $this->statusRepository->findAllByName($name);

        /** @var Status $status */
        foreach ($statusesToDelete as $status) {
            $this->removeStatus(
                $status->getName(),
                $status->getOwner(),
                [],
                new \DateTime(),
            );
        }
    }

    /**
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    public function createContentStatus(
        string $content,
        StatusHolderInterface $holder,
        string $visibility = VisibilityEnum::HIDDEN,
        array $tags = [],
        \DateTime $time = new \DateTime()
    ): ContentStatus {
        /** @var ContentStatus $status */
        $status = $this->createStatusFromName(
            statusName: EquipmentStatusEnum::DOCUMENT_CONTENT,
            holder: $holder,
            tags: $tags,
            time: $time,
            target: null,
            visibility: $visibility
        );
        $status->setContent($content);

        return $this->persist($status);
    }

    private function handleAttemptOnFailure(
        ?Attempt $attempt,
        StatusHolderInterface $holder,
        string $actionName,
        array $tags,
        \DateTime $time
    ): void {
        if ($attempt && $attempt->getAction() !== $actionName) {
            // Re-initialize attempts with new action
            $attempt
                ->setAction($actionName);
            $attempt->getGameVariables()->setValueByName(0, $attempt->getName());
        } elseif ($attempt === null) { // Create Attempt
            $attempt = $this->createAttemptStatus(
                $actionName,
                $holder
            );
        }
        $this->persist($attempt);

        $this->updateCharge($attempt, 1, $tags, $time);
    }

    private function handleAttemptOnSuccess(?Attempt $attempt): void
    {
        if ($attempt !== null) {
            $this->delete($attempt);
        }
    }

    private function createAttemptStatus(string $action, StatusHolderInterface $holder): Attempt
    {
        /** @var ChargeStatusConfig $attemptConfig */
        $attemptConfig = $this->getStatusConfigByNameAndDaedalus(StatusEnum::ATTEMPT, $holder->getDaedalus());

        $attempt = new Attempt($holder, $attemptConfig);
        $attempt->setAction($action);

        return $attempt;
    }

    private function delete(Status $status): void
    {
        $status->getOwner()->removeStatus($status);

        $this->entityManager->remove($status);
        $this->entityManager->flush();
    }
}
