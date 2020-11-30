# Architecture 

## Directory Tree:
    |-- bin/                      
    |-- config/       
    |-- migrations/          
    |-- public/           
    |-- src/            
        |-- Action/
        |-- Daedalus/
            |-- config
            |-- Controller
            |-- Entity
            |-- Event
            |-- Normalizer
            |-- Repository
            |-- Service
            |-- Validator
        |-- Game/
        |-- Item/
        |-- Player/
        |-- Room/
        |-- RoomLog/       
        |-- User/

    |-- tests/              --> Test directory
    |-- .env                --> environment variables
    |-- composer.json        --> dependencies

## Repositories

### bin/
Symfony/php commands, you can run for instance
```
bin/console
bin/phpunit
```
### config/
core config files, you will find every dependencies configuration, the routes definition, database configuration, etc...
### migrations/
doctrine migrations, basically all the sql request to set-up and update the database
documentation: https://symfony.com/doc/master/bundles/DoctrineMigrationsBundle/index.html
### public/
entry point of Symfony, that are the public files and asset that apache can access to  
It is very unlikely that you need to modify something there
### src/
In previous versions of Symfony that would have been bundles
Each folder manage a part, the Game folder is for all the services/entities that are sharad across each
module/folder.
Daedalus folder manage the Daedalus, Player folder manage the Player (etc...)  
#### config
Config for the module/folder
#### Controller
Responsible for declaring the routes (with annotations), it receives the request and send the response
There should be no logic inside the controller except calling some services and verify the request
#### Entity
The class that holds the data, some of them are stored in database
#### Event
Event declaration and event listener/suscriber
#### Normalizer
Normalize the data returned by the controller, basically it transforms an object into an array
This normalization is where we decide which part of the object are retured or not
#### Repository
The interface between the database and the entities, they are tightly coupled to the ORM (doctrine)
If you have some complexe SQL query to do it is the place to do them
#### Service
There you will find the business logic, you call the Repository to retrieve the data in database
And apply the transformation you need.
#### Validator
There you valid the data you receive in the request, are all the required fields there? Do the character exist?

### How do that works?

Let's take the example of a new player is created:
  1. The client send a POST request to /players
  2. The **validator** (in src/Player/Validator) verify that there is a daedalus and a character in the request
They also verify that the character and daedalus exist, the daedalus is not already full, etc...
  If everything is fine then:  
  3. The **controller** (in src/Player/Controller) receive the request, it might check that the user is authorized to access this daedalus, then it will call
a **service** (in src/Player/Service) to create the Player with the argument passed in the request.
  4. The **Service** will perform the creation of the Player, call another **Service** to get the Game and Player configs, and the Random service to perform random stuff
It might also trigger some **events**, like a new Player is created, this event might trigger the **Event** Daedalus is complete, etc...
This **event** will also use the **service** to create a new room log for player awaken  
To finish that, once the **entity** player created, the service will use the **Repository** to save this Player in the database annd returning this Player entity
  5. Once the **service** has finished performing the creating of the character, ot returns an entity **Player**, then the **Controller** will return this Player as response to the request
  6. While creating the Response the **Normalizer** will normalize the Player **entity** into an array, and won't return the satiety for instance as it is an hidden property 
  7. Then the client should have his response

### Actions vs Events

The difference between an action and an event (from a development perspective) is an Action is what a User want to do. An event is something that can be the result of an action,or the change of cycle.
Obvious example:
- Action:
    - Move: The player use a door
    - Shoot a hunter
- Event:
    - The cycle change
    - A player die
    
 For instance a player can make the action 'hit' on an other player, this will trigger the event 'player die'.  
 Less obvious example:   
    A player make the action eat, that trigger the event 'become Dirty'  
There are several grey area still:   
     Player make the action 'repare' on a door, should the repared door trigger the event repared?

#### Action

Create a new Action:
- Create a class that extends [src/Action/Actions/Action.php](./src/Action/Actions/Action.php) abstract class: implement the abstract methods
- Register this action in the [src/Action/Enum/ActionEnum.php](./src/Action/Enum/ActionEnum.php)
- Add the new Class in the [src/Action/config/actions.yaml](./src/Action/config/actions.yaml)

## [Items](./docs/Items.md)


## RoomLogs
When an action, or an event is being performed, a roomLog should be created  
In order to add a roomLog, use the RoomLogService::createLog method
```
public function createLog(string $logKey, Player $player, Room $room, string $visibility, RoomLogParameter $roomLogParameter): RoomLog;
```

## Tests PhpUnit
The test folder is a mirror of the src directory
You can Mock classes/services with [Mockery](https://github.com/mockery/mockery)

You can run a unit test with
```
php bin/phpunit
```

