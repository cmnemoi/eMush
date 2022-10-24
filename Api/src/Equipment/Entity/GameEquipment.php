<?php

namespace Mush\Equipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Entity\StatusTarget;
use Mush\Status\Entity\TargetStatusTrait;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'game_equipment' => GameEquipment::class,
    'door' => Door::class,
    'game_item' => GameItem::class,
])]
class GameEquipment implements StatusHolderInterface, LogParameterInterface, ModifierHolder
{
    use TimestampableEntity;
    use TargetStatusTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\OneToMany(mappedBy: 'gameEquipment', targetEntity: StatusTarget::class, cascade: ['ALL'])]
    private Collection $statuses;

    #[ORM\ManyToOne(targetEntity: Place::class, inversedBy: 'equipments')]
    protected ?Place $place = null;

    #[ORM\ManyToOne(targetEntity: EquipmentConfig::class)]
    protected EquipmentConfig $equipment;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $name;

    #[ORM\OneToMany(mappedBy: 'gameEquipment', targetEntity: Modifier::class)]
    private Collection $modifiers;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    private ?Player $owner = null;

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

    public function addStatus(Status $status): static
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

    public function setHolder(?EquipmentHolderInterface $holder): static
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

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEquipment(): EquipmentConfig
    {
        return $this->equipment;
    }

    public function setEquipment(EquipmentConfig $equipment): static
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getModifiers(): ModifierCollection
    {
        return new ModifierCollection($this->modifiers->toArray());
    }

    public function getAllModifiers(): ModifierCollection
    {
        $allModifiers = new ModifierCollection($this->modifiers->toArray());

        if (($player = $this->getHolder()) instanceof Player) {
            $allModifiers = $allModifiers->addModifiers($player->getModifiers());
        }
        $allModifiers = $allModifiers->addModifiers($this->getPlace()->getModifiers());

        return $allModifiers->addModifiers($this->getPlace()->getDaedalus()->getModifiers());
    }

    public function addModifier(Modifier $modifier): static
    {
        $this->modifiers->add($modifier);

        return $this;
    }

    public function getOwner(): ?Player
    {
        return $this->owner;
    }

    public function setOwner(Player $owner): static
    {
        $this->owner = $owner;

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
        /** @var Status $status */
        foreach ($this->getStatuses() as $status) {
            if (in_array($status->getStatusConfig()->getName(), EquipmentStatusEnum::getOutOfOrderStatuses())) {
                return false;
            }
            if (($status->getStatusConfig()->getName() === EquipmentStatusEnum::ELECTRIC_CHARGES) &&
                 $status instanceof ChargeStatus &&
                 $status->getCharge() === 0
            ) {
                return false;
            }
        }

        return true;
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
