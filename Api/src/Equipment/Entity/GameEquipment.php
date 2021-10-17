<?php

namespace Mush\Equipment\Entity\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Place\Entity\Place;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Entity\StatusTarget;
use Mush\Status\Entity\TargetStatusTrait;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class GameEquipment.
 *
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "game_equipment" = "Mush\Equipment\Entity\GameEquipment",
 *     "door" = "Mush\Equipment\Entity\Door",
 *     "game_item" = "Mush\Equipment\Entity\GameItem"
 * })
 */
class GameEquipment implements StatusHolderInterface, LogParameterInterface, ModifierHolder
{
    use TimestampableEntity;
    use TargetStatusTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Status\Entity\StatusTarget", mappedBy="gameEquipment", cascade={"ALL"})
     */
    private Collection $statuses;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\Place\Entity\Place", inversedBy="equipments")
     */
    protected ?Place $place = null;

    /**
     * @ORM\ManyToOne(targetEntity="EquipmentConfig")
     */
    protected EquipmentConfig $equipment;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $name;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Modifier\Entity\Modifier", mappedBy="gameEquipment")
     */
    private Collection $modifiers;

    /**
     * GameEquipment constructor.
     */
    public function __construct()
    {
        $this->statuses = new ArrayCollection();
        $this->modifiers = new ModifierCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getClassName(): string
    {
        return get_class($this);
    }

    public function getActions(): Collection
    {
        return $this->equipment->getActions();
    }

    /**
     * @return static
     */
    public function addStatus(Status $status): self
    {
        if (!$this->getStatuses()->contains($status)) {
            if (!$statusTarget = $status->getStatusTargetTarget()) {
                $statusTarget = new StatusTarget();
            }
            $statusTarget->setOwner($status);
            $statusTarget->setGameEquipment($this);
            $this->statuses->add($statusTarget);
        }

        return $this;
    }

    public function getPlace(): Place
    {
        if (($holder = $this->getHolder()) === null) {
            throw new \LogicException('Cannot find place of the GameEquipment');
        }

        return $holder->getPlace();
    }

    public function getHolder(): ?EquipmentHolderInterface
    {
        return $this->place;
    }

    /**
     * @return static
     */
    public function setHolder(?EquipmentHolderInterface $holder): self
    {
        if ($holder === null) {
            $this->place = null;

            return $this;
        }

        if (!$holder instanceof Place) {
            throw new UnexpectedTypeException($holder, Place::class);
        }
        if ($holder !== ($oldPlace = $this->getHolder())) {
            if ($oldPlace !== null) {
                $oldPlace->removeEquipment($this);
            }

            $this->place = $holder;

            $holder->addEquipment($this);
        }

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return static
     */
    public function setName(string $name): GameEquipment
    {
        $this->name = $name;

        return $this;
    }

    public function getEquipment(): EquipmentConfig
    {
        return $this->equipment;
    }

    /**
     * @return static
     */
    public function setEquipment(EquipmentConfig $equipment): GameEquipment
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getModifiers(): ModifierCollection
    {
        return new ModifierCollection($this->modifiers->toArray());
    }

    /**
     * @return static
     */
    public function addModifier(Modifier $modifier): GameEquipment
    {
        $this->modifiers->add($modifier);

        return $this;
    }

    public function isBroken(): bool
    {
        return $this
            ->getStatuses()
            ->exists(fn (int $key, Status $status) => ($status->getName() === EquipmentStatusEnum::BROKEN))
            ;
    }

    public function isOperational(): bool
    {
        //@TODO handle other charges
        $chargeStatus = $this->getStatusByName(EquipmentStatusEnum::ELECTRIC_CHARGES);

        if ($chargeStatus === null || !($chargeStatus instanceof ChargeStatus)) {
            return !$this->isBroken();
        }

        return $chargeStatus->getCharge() > 0 && !$this->isBroken();
    }

    public function isBreakable(): bool
    {
        return $this->getEquipment()->isBreakable();
    }

    public function getLogName(): string
    {
        return $this->getName();
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::EQUIPMENT;
    }
}
