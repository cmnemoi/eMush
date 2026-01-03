<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class NpcData
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\OneToOne(inversedBy: 'data', targetEntity: Npc::class, cascade: ['ALL'])]
    private Npc $npc;

    #[ORM\Column(type: 'array', nullable: false, options : ['default' => 'a:0:{}'])]
    private array $memory = [];

    public function __construct(Npc $npc)
    {
        $this->npc = $npc;
    }

    public function getMemory(): array
    {
        return $this->memory;
    }

    public function setMemory(array $memory): self
    {
        $this->memory = $memory;

        return $this;
    }
}
