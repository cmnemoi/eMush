<?php


namespace Mush\Action\Entity;


use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Action
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", nullable=false)
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $name;

    /**
     * @ORM\Column(type="array", nullable=false)
     */
    private array $type = [];

    /**
     * @ORM\OneToMany (targetEntity="Mush\Action\Entity\TargetAction", mappedBy="action")
     */
    private Collection $targetsAction;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $scope;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $successRate = 100;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $injuryRate = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $dirtyRate = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Action
    {
        $this->name = $name;
        return $this;
    }

    public function getType(): array
    {
        return $this->type;
    }

    public function setType(array $type): Action
    {
        $this->type = $type;
        return $this;
    }

    public function getTargetsAction(): Collection
    {
        return $this->targetsAction;
    }

    public function setTargetsAction(Collection $targetsAction): Action
    {
        $this->targetsAction = $targetsAction;
        return $this;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function setScope(string $scope): Action
    {
        $this->scope = $scope;
        return $this;
    }

    public function getSuccessRate(): int
    {
        return $this->successRate;
    }

    public function setSuccessRate(int $successRate): Action
    {
        $this->successRate = $successRate;
        return $this;
    }

    public function getInjuryRate(): int
    {
        return $this->injuryRate;
    }

    public function setInjuryRate(int $injuryRate): Action
    {
        $this->injuryRate = $injuryRate;
        return $this;
    }

    public function getDirtyRate(): int
    {
        return $this->dirtyRate;
    }

    public function setDirtyRate(int $dirtyRate): Action
    {
        $this->dirtyRate = $dirtyRate;
        return $this;
    }
}