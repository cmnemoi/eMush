<?php


namespace Mush\Action\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class TargetAction
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
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $type;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Action\Entity\Action", inversedBy="targetsAction")
     */
    private string $action;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): TargetAction
    {
        $this->name = $name;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): TargetAction
    {
        $this->type = $type;
        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): TargetAction
    {
        $this->action = $action;
        return $this;
    }
}