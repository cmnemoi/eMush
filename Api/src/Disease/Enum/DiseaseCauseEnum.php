<?php

namespace Mush\Disease\Enum;

abstract class DiseaseCauseEnum
{
    public const string CYCLE = 'cycle'; // randomly caused by incidents during cycle change
    public const string CYCLE_LOW_MORALE = 'cycle_low_morale'; // same as above, but special table used on players with low morale
    public const string CONTACT = 'contact'; // airborne transmitted; needs someone to have the disease to transmit to begin with
    public const string EXPLORATION = 'exploration'; // disease event in exploration
    public const string SEX = 'sex'; // sexually transmitted; needs someone to have the disease to transmit to begin with
    public const string TRAUMA = 'trauma'; // witnessing a murder or a death
    public const string ALIEN_FIGHT = 'alien_fight'; // being wounded by a fight event in exploration
    public const string PERISHED_FOOD = 'perished_food'; // eating perished food
    public const string ALIEN_FRUIT_CAUSE = 'alien_fruit_cause'; // can be selected to be caused by alien fruits
    public const string ALIEN_FRUIT_CURE = 'alien_fruit_cure'; // can be selected to be cured by alien fruits
    public const string DRUG_CURE = 'drug_cure'; // can be selected to be cured by drugs
    public const string SPACE_TRAVEL = 'space_travel'; // upon ship movement (either turning or travelling)
    public const string INFECTION = 'infection'; // receiving a spore through most means
    public const string MAKE_SICK = 'make_sick'; // bacterophilia
    public const string SURGERY = 'surgery'; // failing a surgery

    // special reasons, used as tags only
    public const string OVERRODE = 'overrode';
    public const string CONSUMABLE_EFFECT = 'consumable_effect';
    public const string INCUBATING_END = 'incubating_end';
    public const string CAT_ALLERGY = 'cat_allergy_cause';
}
