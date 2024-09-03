<?php

declare(strict_types=1);

namespace Mush\Daedalus\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\TitleConfig;

#[ORM\Entity]
class TitlePriority
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Daedalus::class, inversedBy: 'titlePriorities')]
    private Daedalus $daedalus;

    #[ORM\Column(type: 'string', length: 255, nullable: false, options: ['default' => ''])]
    private string $name = '';

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => 'a:0:{}'])]
    private array $priority = [];

    public function __construct(TitleConfig $titleConfig, Daedalus $daedalus)
    {
        $this->daedalus = $daedalus;
        $this->name = $titleConfig->getName();
        $this->priority = $titleConfig->getPriority();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPriority(): array
    {
        return $this->priority;
    }
}
