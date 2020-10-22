<?php


namespace Mush\Item\Enum;


class PlantStatusEnum
{
    public const YOUNG = 'young'; //This plant does not produce anything yet until it matures. This takes anywhere from 1-48 cycles depending on the plant type.
    public const THIRSTY = 'Thirsty'; //his plant can still produce Oxygen on day change but no fruit. It will be dried out the next day.
    public const DRIED = 'dried'; //If left unwatered, this plant will die on day change.
    public const DISEASED = 'diseased'; //This plant is unable to produce anything until treated.
}