<?php

declare(strict_types=1);

namespace Mush\Communications\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Communications\ValueObject\SignalStrength;
use Mush\Daedalus\Entity\Daedalus;

#[ORM\Entity]
class LinkWithSol
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $strength;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isEstablished;

    private int $daedalusId;

    #[ORM\OneToOne(targetEntity: Daedalus::class)]
    #[ORM\JoinColumn(name: 'daedalus_id')]
    private Daedalus $daedalus;

    public function __construct(int $strength, bool $isEstablished, int $daedalusId)
    {
        $this->strength = SignalStrength::create($strength)->value;
        $this->isEstablished = $isEstablished;
        $this->daedalusId = $daedalusId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStrength(): int
    {
        return $this->strength;
    }

    public function getDaedalusId(): int
    {
        return $this->daedalusId;
    }

    public function isEstablished(): bool
    {
        return $this->isEstablished;
    }

    public function increaseStrength(int $strengthIncrease): void
    {
        $this->strength = $this->getStrengthAsValueObject()->increase($strengthIncrease)->value;
    }

    public function markAsEstablished(): void
    {
        $this->isEstablished = true;
    }

    private function getStrengthAsValueObject(): SignalStrength
    {
        return SignalStrength::create($this->strength);
    }
}
