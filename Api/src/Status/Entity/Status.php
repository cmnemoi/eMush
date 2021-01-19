<?php

namespace Mush\Status\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;

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
 *     "medical_condition" = "Mush\Status\Entity\MedicalCondition",
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
    protected ?string $visibility = null;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Status\Entity\StatusTarget")
     */
    protected ?StatusTarget $target = null;

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

    public function getVisibility(): ?string
    {
        return $this->visibility;
    }

    /**
     * @return static
     */
    public function setVisibility(?string $visibility): Status
    {
        $this->visibility = $visibility;

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
        if ($room = $this->target->getRoom()) {
            return $room;
        }

        throw new \LogicException('There should always be a target on a status target');
    }

    /**
     * @return static
     */
    public function setTarget(?StatusHolderInterface $target): Status
    {
        $statusTarget = new StatusTarget();
        if ($target instanceof Player) {
            $statusTarget->setPlayer($target);
        } elseif ($target instanceof GameEquipment) {
            $statusTarget->setGameEquipment($target);
        } elseif ($target instanceof Room) {
            $statusTarget->setRoom($target);
        } else {
            $statusTarget = null;
        }

        $this->target = $statusTarget;

        return $this;
    }
}
