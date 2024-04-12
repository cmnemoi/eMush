<?php

namespace Mush\Daedalus\Entity\Dto;

use Mush\Game\Entity\GameConfig;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DaedalusRequest.
 */
class DaedalusCreateRequest
{
    /**
     * @Assert\NotBlank
     */
    private ?string $name = null;

    /**
     * @Assert\NotNull
     */
    private ?GameConfig $config = null;

    /**
     * @Assert\NotBlank
     */
    private ?string $language = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getConfig(): ?GameConfig
    {
        return $this->config;
    }

    public function setConfig(?GameConfig $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }
}
