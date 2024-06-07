<?php

namespace Mush\MetaGame\Entity;

interface SanctionEvidenceInterface
{
    public function getMessage(): ?string;

    public function getId(): int;

    public function getClassName(): string;
}
