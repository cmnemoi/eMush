<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity\Mechanics;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Game\Entity\Collection\ProbaCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    paginationItemsPerPage: 25,
    normalizationContext: ['groups' => ['ration_read']],
    denormalizationContext: ['groups' => ['ration_write']],
    operations: [
        new GetCollection(filters: ['default.search_filter', 'default.order_filter']),
        new Post(security: 'is_granted("ROLE_ADMIN")'),
        new Get(security: 'is_granted("ROLE_ADMIN")'),
        new Put(security: 'is_granted("ROLE_ADMIN")'),
    ],
)]
#[ORM\Entity]
class Ration extends EquipmentMechanic
{
    #[Groups(['ration_read', 'ration_write'])]
    #[ORM\Column(type: 'integer', nullable: true)]
    protected ?int $satiety = null;

    #[Groups(['ration_read', 'ration_write'])]
    #[ORM\Column(type: 'boolean', nullable: false)]
    protected bool $isPerishable = true;

    /**
     * Possibilities are stored as key, array value represent the probability to get the key value.
     *
     * @see ProbaCollection
     */
    #[Groups(['ration_read', 'ration_write'])]
    #[ORM\Column(type: 'array', nullable: false)]
    private array $moralPoints;

    #[Groups(['ration_read', 'ration_write'])]
    #[ORM\Column(type: 'array', nullable: false)]
    private array $actionPoints;

    #[Groups(['ration_read', 'ration_write'])]
    #[ORM\Column(type: 'array', nullable: false)]
    private array $movementPoints;

    #[Groups(['ration_read', 'ration_write'])]
    #[ORM\Column(type: 'array', nullable: false)]
    private array $healthPoints;

    /** Store any extra effect the food has as key with the chance to get it as value */
    #[Groups(['ration_read', 'ration_write'])]
    #[ORM\Column(type: 'array', nullable: false)]
    private array $extraEffects;

    public function __construct()
    {
        parent::__construct();
        $this->actionPoints = [0 => 1];
        $this->movementPoints = [0 => 1];
        $this->moralPoints = [0 => 1];
        $this->healthPoints = [0 => 1];
        $this->extraEffects = [];
    }

    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::RATION;

        return $mechanics;
    }

    public function getActionPoints(): ProbaCollection
    {
        return new ProbaCollection($this->actionPoints);
    }

    public function setActionPoints(array $actionPoints): static
    {
        $this->actionPoints = $actionPoints;

        return $this;
    }

    public function getMovementPoints(): ProbaCollection
    {
        return new ProbaCollection($this->movementPoints);
    }

    public function setMovementPoints(array $movementPoints): static
    {
        $this->movementPoints = $movementPoints;

        return $this;
    }

    public function getHealthPoints(): ProbaCollection
    {
        return new ProbaCollection($this->healthPoints);
    }

    public function setHealthPoints(array $healthPoints): static
    {
        $this->healthPoints = $healthPoints;

        return $this;
    }

    public function getMoralPoints(): ProbaCollection
    {
        return new ProbaCollection($this->moralPoints);
    }

    public function setMoralPoints(array $moralPoints): static
    {
        $this->moralPoints = $moralPoints;

        return $this;
    }

    public function getSatiety(): ?int
    {
        return $this->satiety;
    }

    public function setSatiety(?int $satiety): static
    {
        $this->satiety = $satiety;

        return $this;
    }

    public function getExtraEffects(): ProbaCollection
    {
        return new ProbaCollection($this->extraEffects);
    }

    public function setExtraEffects(array $extraEffects): static
    {
        $this->extraEffects = $extraEffects;

        return $this;
    }

    public function getIsPerishable(): bool
    {
        return $this->isPerishable;
    }

    public function setIsPerishable(bool $isPerishable): static
    {
        $this->isPerishable = $isPerishable;

        return $this;
    }
}
