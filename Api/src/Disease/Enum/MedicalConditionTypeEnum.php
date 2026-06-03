<?php

declare(strict_types=1);

namespace Mush\Disease\Enum;

abstract class MedicalConditionTypeEnum
{
    public const string DISEASE = 'disease';
    public const string DISORDER = 'disorder';
    public const string INJURY = 'injury';
    public const string CURE = 'cure';
}
