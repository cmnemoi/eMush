<?php

namespace Mush\Status\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Equipment\Entity\Config\GameEquipment;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;

/**
 * Class Status.
 *
 * @ORM\Entity()
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "status" = "Mush\Status\Entity\Status",
 *     "charge_status" = "Mush\Status\Entity\ChargeStatus",
 *     "attempt" = "Mush\Status\Entity\Attempt",
 *     "content_status" = "Mush\Status\Entity\ContentStatus",
 * })
 */
class Status
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    protected ?int $id = null;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected ?string $name = null;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected string $visibility = VisibilityEnum::PUBLIC;

    /**
     * @ORM\OneToOne(targetEntity="Mush\Status\Entity\StatusTarget", cascade={"ALL"}, inversedBy="owner")
     */
    protected ?StatusTarget $owner;

    /**
     * @ORM\OneToOne(targetEntity="Mush\Status\Entity\StatusTarget", cascade={"ALL"}, inversedBy="target")
     */
    protected ?StatusTarget $target = null;

    public function __construct(StatusHolderInterface $statusHolder)
    {
        $this->setOwner($statusHolder);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return static
     */
    public function setName(?string $name): Status
    {
        $this->name = $name;

        return $this;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    /**
     * @return static
     */
    public function setVisibility(string $visibility): Status
    {
        $this->visibility = $visibility;

        return $this;
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

        throw new \LogicException('There should always be a target on a status target');
    }

    private function setOwner(StatusHolderInterface $owner): Status
    {
        $statusOwner = new StatusTarget();
        $statusOwner->setOwner($this);
        if ($owner instanceof Player) {
            $statusOwner->setPlayer($owner);
        } elseif ($owner instanceof GameEquipment) {
            $statusOwner->setGameEquipment($owner);
        } elseif ($owner instanceof Place) {
            $statusOwner->setPlace($owner);
        }

        $this->owner = $statusOwner;

        return $this;
    }

    public function setTargetOwner(StatusTarget $owner): Status
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

        throw new \LogicException('There should always be a target on a status target');
    }

    /**
     * @return static
     */
    public function setTarget(?StatusHolderInterface $target): Status
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

    public function setStatusTargetOwner(StatusTarget $statusTarget): Status
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

    public function setStatusTargetTarget(StatusTarget $statusTarget): Status
    {
        $this->target = $statusTarget;

        return $this;
    }

    public function getStatusTargetTarget(): ?StatusTarget
    {
        return $this->target;
    }

    public function delete(): Status
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
}
