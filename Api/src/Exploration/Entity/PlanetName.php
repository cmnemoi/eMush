<?php

declare(strict_types=1);

namespace Mush\Exploration\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class PlanetName
{
    public const NUMBER_OF_PREFIXES = 34;
    public const NUMBER_OF_FIRST_SYLLABLES = 47;
    public const NUMBER_OF_SECOND_SYLLABLES = 25;
    public const NUMBER_OF_THIRD_SYLLABLES = 25;
    public const NUMBER_OF_FOURTH_SYLLABLES = 33;
    public const NUMBER_OF_FIFTH_SYLLABLES = 34;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $prefix = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $firstSyllable = 0;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $secondSyllable = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $thirdSyllable = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $fourthSyllable = 0;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $fifthSyllable = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getPrefix(): ?int
    {
        return $this->prefix;
    }

    public function setPrefix(?int $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getFirstSyllable(): int
    {
        return $this->firstSyllable;
    }

    public function setFirstSyllable(int $firstSyllable): self
    {
        $this->firstSyllable = $firstSyllable;

        return $this;
    }

    public function getSecondSyllable(): ?int
    {
        return $this->secondSyllable;
    }

    public function setSecondSyllable(?int $secondSyllable): self
    {
        $this->secondSyllable = $secondSyllable;

        return $this;
    }

    public function getThirdSyllable(): ?int
    {
        return $this->thirdSyllable;
    }

    public function setThirdSyllable(int $thirdSyllable): self
    {
        $this->thirdSyllable = $thirdSyllable;

        return $this;
    }

    public function getFourthSyllable(): int
    {
        return $this->fourthSyllable;
    }

    public function setFourthSyllable(int $fourthSyllable): self
    {
        $this->fourthSyllable = $fourthSyllable;

        return $this;
    }

    public function getFifthSyllable(): ?int
    {
        return $this->fifthSyllable;
    }

    public function setFifthSyllable(?int $fifthSyllable): self
    {
        $this->fifthSyllable = $fifthSyllable;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'type' => self::class,
            'prefix' => $this->prefix,
            'first_syllable' => $this->firstSyllable,
            'second_syllable' => $this->secondSyllable,
            'third_syllable' => $this->thirdSyllable,
            'fourth_syllable' => $this->fourthSyllable,
            'fifth_syllable' => $this->fifthSyllable,
        ];
    }
}
