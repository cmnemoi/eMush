<?php


namespace Mush\Action\Validator;


class Fuel extends ClassConstraint
{
    public string $message = 'cannot add or remove fuel';
    public bool $retrieve = true; //If it's not retrieve the then it's insert
}
