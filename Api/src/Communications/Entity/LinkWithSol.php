<?php

declare(strict_types=1);

namespace Mush\Communications\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Communications\ValueObject\LinkStrength;
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

    public function __construct(int $daedalusId, int $strength = 0, bool $isEstablished = false)
    {
        $this->strength = LinkStrength::create($strength)->value;
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

    /**
     * @deprecated should be used only in Doctrine repositories
     */
    public function setDaedalusId(int $daedalusId): void
    {
        $this->daedalusId = $daedalusId;
    }

    /**
     * @deprecated Should be used only in Doctrine repositories. Use getDaedalusId() instead.
     */
    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    /**
     * @deprecated should be used only in Doctrine repositories
     */
    public function setDaedalus(Daedalus $daedalus): void
    {
        $this->daedalus = $daedalus;
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

    private function getStrengthAsValueObject(): LinkStrength
    {
        return LinkStrength::create($this->strength);
    }
}
