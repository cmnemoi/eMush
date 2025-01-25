# Add a new Weapon Effect

This guide explains how to implement brand new weapon effects.

## Step by step guide

1. Create a new Entity in `Api/src/Equipment/Entity/Config/WeaponEffect`:
   ```php
   namespace Mush\Equipment\Entity\Config\WeaponEffect;

   use Doctrine\ORM\Mapping as ORM;
   use Mush\Game\Entity\AbstractEventConfig;

   #[ORM\Entity]
   class YourWeaponEffectConfig extends AbstractEventConfig implements QuantityWeaponEffectConfig // or other interfaces if needed
   {
       // Add your effect specific properties
       #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
       private int $quantity = 0;

       public function __construct(
           string $name,
           string $eventName,
           int $quantity = 0,
       ) {
           parent::__construct($name, $eventName);
           $this->quantity = $quantity;
       }

       // Add getters and setters
       public function getQuantity(): int
       {
           return $this->quantity;
       }

       // Add DTO update method
       public function updateFromDto(YourWeaponEffectConfigDto $dto): void
       {
           $this->name = $dto->name;
           $this->eventName = $dto->eventName;
           $this->quantity = $dto->quantity;
       }
   }
   ```

2. Create the associated DTO in `Api/src/Equipment/Entity/Dto/WeaponEffect`:
   ```php
   namespace Mush\Equipment\Entity\Dto\WeaponEffect;

   final readonly class YourWeaponEffectConfigDto extends WeaponEffectDto
   {
       public function __construct(
           string $name,
           string $eventName,
           public int $quantity = 0,
       ) {
           parent::__construct($name, $eventName);
       }
   }
   ```

3. Create a handler that extends `AbstractWeaponEffectHandler` in `Api/src/Equipment/WeaponEffect`:
   ```php
   namespace Mush\Equipment\WeaponEffect;

   use Mush\Equipment\Enum\WeaponEffectEnum;
   use Mush\Equipment\Event\WeaponEffect;

   final readonly class YourWeaponEffectHandler extends AbstractWeaponEffectHandler
   {
       public function getName(): string
       {
           return WeaponEffectEnum::YOUR_EFFECT->toString();
       }

       public function handle(WeaponEffect $effect): void
       {
           // Implement your effect logic here
       }
   }
   ```

4. Add your effect name to `WeaponEffectEnum` in `Api/src/Equipment/Enum/WeaponEffectEnum.php`:
   ```php
   case YOUR_EFFECT = 'your_effect';
   ```

5. Configure your effect in `Api/src/Game/ConfigData/EventConfigData.php`:
   ```php
   new YourWeaponEffectConfigDto(
       name: WeaponEffectEnum::YOUR_EFFECT->toString(),
       eventName: WeaponEffectEnum::YOUR_EFFECT->toString(),
       quantity: 1, // or other properties specific to your effect
   ),
   ```

6. Add test fixtures in `Api/src/Equipment/DataFixtures/WeaponEffectConfigFixtures.php`

## Available Interfaces

When creating your weapon effect config, you can implement these interfaces based on your needs:

- **QuantityWeaponEffectConfig**: For effects that need a quantity value (like damage modifiers)
- **BackfireWeaponEffectConfig**: For effects that can backfire on the shooter
- **RandomWeaponEffectConfig**: For effects with random outcomes

## Example

Here's an example of implementing a weapon effect that doubles the damage:

1. Create the config entity:
```php
#[ORM\Entity]
class DoubleDamageWeaponEffectConfig extends AbstractEventConfig implements QuantityWeaponEffectConfig
{
    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 2])]
    private int $quantity = 2;

    public function __construct(
        string $name,
        string $eventName,
        int $quantity = 2,
    ) {
        parent::__construct($name, $eventName);
        $this->quantity = $quantity;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function updateFromDto(DoubleDamageWeaponEffectConfigDto $dto): void
    {
        $this->name = $dto->name;
        $this->eventName = $dto->eventName;
        $this->quantity = $dto->quantity;
    }
}
```

2. Create the DTO:
```php
final readonly class DoubleDamageWeaponEffectConfigDto extends WeaponEffectDto
{
    public function __construct(
        string $name,
        string $eventName,
        public int $quantity = 2,
    ) {
        parent::__construct($name, $eventName);
    }
}
```

3. Create the handler:
```php
final readonly class DoubleDamageWeaponEffectHandler extends AbstractWeaponEffectHandler
{
    public function getName(): string
    {
        return WeaponEffectEnum::DOUBLE_DAMAGE->toString();
    }

    public function handle(WeaponEffect $effect): void
    {
        $effect->modifyDamage($effect->getDamage() * 2);
    }
}
```

4. Add to WeaponEffectEnum:
```php
case DOUBLE_DAMAGE = 'double_damage';
```

5. Configure in EventConfigData:
```php
new DoubleDamageWeaponEffectConfigDto(
    name: WeaponEffectEnum::DOUBLE_DAMAGE->toString(),
    eventName: WeaponEffectEnum::DOUBLE_DAMAGE->toString(),
    quantity: 2,
),
```

For information on how to add this effect to a weapon event, see [WEAPON_EVENT_README.md](./WEAPON_EVENT_README.md).
