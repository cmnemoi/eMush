<?php

declare(strict_types=1);

namespace Mush\Communications\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;

#[ORM\Entity]
class NeronVersion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'integer', length: 255, nullable: false, options: ['default' => 1])]
    private int $major;

    #[ORM\Column(type: 'integer', length: 255, nullable: false, options: ['default' => 0])]
    private int $minor;

    private int $daedalusId;

    #[ORM\OneToOne(targetEntity: Daedalus::class)]
    #[ORM\JoinColumn(name: 'daedalus_id')]
    private Daedalus $daedalus;

    public function __construct(int $daedalusId, int $major = 1, int $minor = 0)
    {
        $this->major = $major;
        $this->minor = $minor;
        $this->daedalusId = $daedalusId;
    }

    public function getMajor(): int
    {
        return $this->major;
    }

    public function getDaedalusId(): int
    {
        return $this->daedalusId;
    }

    public function increment(int $minorIncrement): bool
    {
        $majorUpdated = false;
        if ($this->minor + $minorIncrement >= 100) {
            ++$this->major;
            $majorUpdated = true;
        }

        $this->minor = ($this->minor + $minorIncrement) % 100;

        return $majorUpdated;
    }

    public function toString(): string
    {
        $minor = str_pad((string) $this->minor, 2, '0', STR_PAD_LEFT);

        return "{$this->major}.{$minor}";
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
}
