<?php

namespace Mush\Status\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Hunter\Entity\Hunter;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Project\Entity\Project;
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
class Status
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
        if ($project = $this->owner->getProject()) {
            return $project;
        }

        throw new \LogicException('There should always be a target on a status target');
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
        if ($project = $this->target->getProject()) {
            return $project;
        }

        throw new \LogicException('There should always be a target on a status target');
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
        } elseif ($target instanceof Project) {
            $statusTarget->setProject($target);
        } else {
            $statusTarget = null;
        }

        $this->target = $statusTarget;

        return $this;
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
        } elseif ($owner instanceof Project) {
            $statusOwner->setProject($owner);
        }

        $this->owner = $statusOwner;

        return $this;
    }
}
