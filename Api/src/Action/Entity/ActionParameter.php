<?php

namespace Mush\Action\Entity;

interface ActionParameter
{
    public function getClassName(): string;

    public function getLogName(): string;

    public function getLogKey(): string;
}
