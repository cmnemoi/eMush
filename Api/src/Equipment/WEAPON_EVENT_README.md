# Add a new Weapon Event

This guide explains how to add new Weapon Events to a weapon, alongside their associated Weapon Effects.

## Step by step guide

1. Add the weapon event name in [WeaponEventEnum](./Enum/WeaponEventEnum.php):
```php
case YOUR_WEAPON_EVENT = 'your_weapon_event';
```

2. Add the weapon event configuration in [EventConfigData](../Game/ConfigData/EventConfigData.php):
```php
new WeaponEventConfigDto(
    name: WeaponEventEnum::YOUR_WEAPON_EVENT->toString(),
    eventName: WeaponEventEnum::YOUR_WEAPON_EVENT->toString(),
    eventType: WeaponEventType::NORMAL, // or CRITIC, MISS, FUMBLE
    effectKeys: [
        WeaponEffectEnum::YOUR_EFFECT->toString(),
        // Add more effects if needed
    ]
)
```

3. If you need new effects, add them in [WeaponEffectEnum](./Enum/WeaponEffectEnum.php):
```php
case YOUR_EFFECT = 'your_effect';
```

4. Configure the effect in [EventConfigData](../Game/ConfigData/EventConfigData.php):
```php
// Example for a damage effect
new ModifyDamageWeaponEffectConfigDto(
    name: WeaponEffectEnum::YOUR_EFFECT->toString(),
    eventName: WeaponEffectEnum::MODIFY_DAMAGE->toString(),
    quantity: 1,
),
```

5. Add the event to your weapon in [MechanicsData](./ConfigData/MechanicsData.php):
```php
'successfulEventKeys' => [
    WeaponEventEnum::YOUR_WEAPON_EVENT->value => 75, // weight (75)
],
// Or for failed events:
'failedEventKeys' => [
    WeaponEventEnum::YOUR_WEAPON_EVENT->value => 25, // weight (25)
],
```

## Available Effect Types

You can use these effect types when configuring weapon effects:

- **ModifyDamageWeaponEffectConfigDto**: Modify the damage dealt
- **ModifyMaxDamageWeaponEffectConfigDto**: Modify the maximum possible damage
- **BreakWeaponEffectConfigDto**: Break the weapon
- **DestroyWeaponEffectConfigDto**: Destroy the weapon
- **DropWeaponEffectConfigDto**: Make the shooter drop their weapon
- **InflictInjuryWeaponEffectConfigDto**: Inflict a specific injury
- **InflictRandomInjuryWeaponEffectConfigDto**: Inflict a random injury
- **OneShotWeaponEffectConfigDto**: Instant kill effect
- **RemoveActionPointsWeaponEffectConfigDto**: Remove action points
- **MultiplyDamageOnMushTargetWeaponEffectConfigDto**: Multiply damage against Mush targets

## Example

Here's an example of adding a new weapon event that deals 2 extra damage and has a 30% chance to inflict head trauma injury:

1. Add the event enum in [WeaponEventEnum](./Enum/WeaponEventEnum.php):
```php
case SUPER_SHOT = 'super_shot';
```

2. Add the effect enum in [WeaponEffectEnum](./Enum/WeaponEffectEnum.php):
```php
case SUPER_DAMAGE = 'super_damage';
case SUPER_INJURY = 'super_injury';
```

3. Configure the event in [EventConfigData](../Game/ConfigData/EventConfigData.php):
```php
new WeaponEventConfigDto(
    name: WeaponEventEnum::SUPER_SHOT->toString(),
    eventName: WeaponEventEnum::SUPER_SHOT->toString(),
    eventType: WeaponEventType::CRITIC,
    effectKeys: [
        WeaponEffectEnum::SUPER_DAMAGE->toString(),
        WeaponEffectEnum::SUPER_INJURY->toString(),
    ]
),
```

4. Configure the effects in [EventConfigData](../Game/ConfigData/EventConfigData.php):
```php
new ModifyDamageWeaponEffectConfigDto(
    name: WeaponEffectEnum::SUPER_DAMAGE->toString(),
    eventName: WeaponEffectEnum::MODIFY_DAMAGE->toString(),
    quantity: 2,
),
new InflictInjuryWeaponEffectConfigDto(
    name: WeaponEffectEnum::SUPER_INJURY->toString(),
    eventName: WeaponEffectEnum::INFLICT_INJURY->toString(),
    injuryName: InjuryEnum::HEAD_TRAUMA,
    triggerRate: 30, // 30% chance
),
```

5. Add to weapon in [MechanicsData](./ConfigData/MechanicsData.php):
```php
'successfulEventKeys' => [
    WeaponEventEnum::SUPER_SHOT->value => 10, // weighted chance to trigger
],
```
