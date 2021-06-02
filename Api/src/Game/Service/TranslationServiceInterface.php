<?php

namespace Mush\Game\Service;

interface TranslationServiceInterface
{
    public function getTranslateParameters(array $parameters): array;
}
