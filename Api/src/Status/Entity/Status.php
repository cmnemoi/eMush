<?php

namespace Mush\Status\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionProviderInterface;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionProviderOperationalStateEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Hunter\Entity\Hunter;
use Mush\Modifier\Entity\ModifierProviderInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\Status\Entity\Config\StatusConfig;

#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'status' => Status::class,
    'charge_status' => ChargeStatus::class,
    'attempt' => Attempt::class,
    'content_status' => ContentStatus::class,
])]
#[ORM\UniqueConstraint(
    columns: ['owner_id', 'target_id', 'status_config_id']
)]
class Status implements ActionProviderInterface, ModifierProviderInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    protected ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'owner', targetEntity: StatusTarget::class, cascade: ['ALL'])]
    protected ?StatusTarget $owner;

    #[ORM\OneToOne(inversedBy: 'target', targetEntity: StatusTarget::class, cascade: ['ALL'])]
    protected ?StatusTarget $target = null;

    #[ORM\ManyToOne(targetEntity: StatusConfig::class)]
    protected StatusConfig $statusConfig;

    public function __construct(StatusHolderInterface $statusHolder, StatusConfig $statusConfig)
    {
        $this->setOwner($statusHolder);
        $this->statusConfig = $statusConfig;
    }

    public static function createNull(): self
    {
        $status = new self(statusHolder: Player::createNull(), statusConfig: StatusConfig::createNull());
        $status->setId(0);

        return $status;
    }

    public function getStatusConfig(): StatusConfig
    {
        return $this->statusConfig;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->statusConfig->getStatusName();
    }

    public function getVisibility(): string
    {
        return $this->statusConfig->getVisibility();
    }

    public function getOwner(): StatusHolderInterface
    {
        if ($this->owner === null) {
            throw new \LogicException("This status should be deleted, id : {$this->getId()}");
        }

        if ($player = $this->owner->getPlayer()) {
            return $player;
        }
        if ($equipment = $this->owner->getGameEquipment()) {
            return $equipment;
        }
        if ($place = $this->owner->getPlace()) {
            return $place;
        }
        if ($hunter = $this->owner->getHunter()) {
            return $hunter;
        }
        if ($daedalus = $this->owner->getDaedalus()) {
            return $daedalus;
        }

        throw new \LogicException('There should always be a target on a status target');
    }

    public function getVisibleOwner(): VisibleStatusHolderInterface
    {
        $owner = $this->getOwner();

        if (!$owner instanceof VisibleStatusHolderInterface) {
            throw new \LogicException('Owner is not a VisibleStatusHolderInterface');
        }

        return $owner;
    }

    public function setTargetOwner(StatusTarget $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getTarget(): ?StatusHolderInterface
    {
        if ($this->target === null) {
            return null;
        }

        if ($player = $this->target->getPlayer()) {
            return $player;
        }
        if ($equipment = $this->target->getGameEquipment()) {
            return $equipment;
        }
        if ($place = $this->target->getPlace()) {
            return $place;
        }

        throw new \LogicException("Status {$this->getName()} target class is not supported ({$this->target->getClassName()})");
    }

    /**
     * @return static
     */
    public function setTarget(?StatusHolderInterface $target): self
    {
        $statusTarget = new StatusTarget();
        $statusTarget->setTarget($this);

        if ($target instanceof Player) {
            $statusTarget->setPlayer($target);
        } elseif ($target instanceof GameEquipment) {
            $statusTarget->setGameEquipment($target);
        } elseif ($target instanceof Place) {
            $statusTarget->setPlace($target);
        } else {
            $statusTarget = null;
        }

        $this->target = $statusTarget;

        return $this;
    }

    public function getTargetOrThrow(): StatusHolderInterface
    {
        if ($this->target === null) {
            throw new \RuntimeException("Status {$this->getName()} has no target.");
        }

        if ($player = $this->target->getPlayer()) {
            return $player;
        }
        if ($equipment = $this->target->getGameEquipment()) {
            return $equipment;
        }
        if ($place = $this->target->getPlace()) {
            return $place;
        }

        throw new \LogicException("Status {$this->getName()} has unhandled target type ({$this->target->getClassName()})");
    }

    public function getPlayerTargetOrThrow(): Player
    {
        $target = $this->getTargetOrThrow();

        return $target instanceof Player ? $target : throw new \RuntimeException("Status {$this->getName()} target is not a Player, but {$target->getClassName()}");
    }

    public function getPlaceTargetOrThrow(): Place
    {
        $target = $this->getTargetOrThrow();

        return $target instanceof Place ? $target : throw new \RuntimeException("Status {$this->getName()} target is not a Place, but {$target->getClassName()}");
    }

    public function setStatusTargetOwner(StatusTarget $statusTarget): self
    {
        $this->owner = $statusTarget;

        return $this;
    }

    public function getStatusTargetOwner(): StatusTarget
    {
        if ($this->owner === null) {
            throw new \LogicException("This status should be deleted, id : {$this->getId()}");
        }

        return $this->owner;
    }

    public function setStatusTargetTarget(StatusTarget $statusTarget): self
    {
        $this->target = $statusTarget;

        return $this;
    }

    public function getStatusTargetTarget(): ?StatusTarget
    {
        return $this->target;
    }

    public function delete(): self
    {
        if ($this->owner !== null) {
            $this->owner->removeStatusLinksTarget();
            $this->owner = null;
        }
        if ($this->target !== null) {
            $this->target->removeStatusLinksTarget();
            $this->target = null;
        }

        return $this;
    }

    public function getClassName(): string
    {
        return static::class;
    }

    public function getUsedCharge(string $actionName): ?ChargeStatus
    {
        return null;
    }

    public function getOperationalStatus(string $actionName): ActionProviderOperationalStateEnum
    {
        return ActionProviderOperationalStateEnum::OPERATIONAL;
    }

    public function getAllModifierConfigs(): ArrayCollection
    {
        return new ArrayCollection($this->statusConfig->getModifierConfigs()->toArray());
    }

    public function getProvidedActions(ActionHolderEnum $actionTarget, array $actionRanges): Collection
    {
        $actions = [];

        /** @var ActionConfig $actionConfig */
        foreach ($this->statusConfig->getActionConfigs() as $actionConfig) {
            if (
                $actionConfig->getDisplayHolder() === $actionTarget
                && \in_array($actionConfig->getRange(), $actionRanges, true)
            ) {
                $action = new Action();
                $action->setActionProvider($this)->setActionConfig($actionConfig);

                $actions[] = $action;
            }
        }

        return new ArrayCollection($actions);
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::STATUS;
    }

    public function getLogName(): string
    {
        return $this->getName();
    }

    public function canPlayerReach(Player $player): bool
    {
        $holder = $this->getOwner();

        if ($holder instanceof Daedalus) {
            return true;
        }
        if ($holder instanceof ActionProviderInterface) {
            return $holder->canPlayerReach($player);
        }

        return false;
    }

    public function isNull(): bool
    {
        return $this->id === 0 || $this->statusConfig->isNull();
    }

    private function setOwner(StatusHolderInterface $owner): self
    {
        $statusOwner = new StatusTarget();
        $statusOwner->setOwner($this);
        if ($owner instanceof Player) {
            $statusOwner->setPlayer($owner);
        } elseif ($owner instanceof GameEquipment) {
            $statusOwner->setGameEquipment($owner);
        } elseif ($owner instanceof Place) {
            $statusOwner->setPlace($owner);
        } elseif ($owner instanceof Hunter) {
            $statusOwner->setHunter($owner);
        } elseif ($owner instanceof Daedalus) {
            $statusOwner->setDaedalus($owner);
        }

        $this->owner = $statusOwner;

        return $this;
    }

    private function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }
}
