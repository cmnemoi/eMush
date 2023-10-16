# Architecture 

## Directory Tree:
    |-- bin/                      
    |-- config/       
    |-- migrations/          
    |-- public/           
    |-- src/            
        |-- Action/
        |-- Alert/
        |-- Communication/
        |-- Daedalus/
            |-- config
            |-- Controller
            |-- ConfigData
            |-- DataFixtures
            |-- Entity
            |-- Enum
            |-- Event
            |-- Listener
            |-- Normalizer
            |-- Repository
            |-- Service
            |-- Validator
        |-- Disease/
        |-- Equipment/
        |-- Game/
        |-- MetaGame/
        |-- Modifier/
        |-- Place/
        |-- Player/
        |-- RoomLog/       
        |-- Status/
        |-- User/

    |-- tests/              --> Test directory
    |-- .env                --> environment variables
    |-- composer.json        --> dependencies

## Repositories

### bin/
Symfony/php commands, you can run for instance
```
bin/console
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
Each folder manage a part, the Game folder is for all the services/entities that are shared across each
module/folder.
Daedalus folder manage the Daedalus, Player folder manage the Player (etc...)  
#### config
Config for the module/folder
#### Controller
Responsible for declaring the routes (with annotations), it receives the request and send the response
There should be no logic inside the controller except calling some services and verify the request
#### ConfigData
Store all the initialization data.
This folder can be easily change to tweak game parameters such as action cost, intensity of effects...
#### DataFixtures
Same as ConfigData but for local development, it is used to populate the database with some data
Should not be used in production (work in progress)
#### Entity
The class that holds the data, some of them are stored in database
#### Enum
Store the strings, ex : key for the equipments
#### Event
Event declaration
#### Listener
Event listener/suscriber
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

The difference between an action and an event (from a development perspective) is an Action is what a User want to do.
An event is something that can be the result of an action, or the change of cycle.
Obvious example:
- Action:
    - Move: The player use a door
    - Shoot a hunter
- Event:
    - The cycle change
    - A player die
    
 For instance a player can make the action 'hit' on another player, this will trigger the event 'player die'.  
 Less obvious example:   
    A player make the action eat, that trigger the event 'become Dirty'

## Module documentation
- [Action](./src/Action/README.md): handle actions performed by the player
- [Alert](./src/Alert/README.md): track Daedalus and crew critical points
- [Communication](./src/Communication/README.md): handle chat between players
- [Daedalus](./src/Daedalus/README.md)
- [Disease](./src/Disease/README.md)
- [Equipment](./src/Equipment/README.md)
- [Game](./src/Game/README.md)
- [MetaGame](./src/MetaGame/README.md)
- [Modifier](./src/Modifier/README.md)
- [Place](./src/Place/README.md)
- [Player](./src/Player/README.md)
- [RoomLog](./src/RoomLog/README.md)
- [Status](./src/Status/README.md)
- [User](./src/User/README.md)

## Useful commands

- `composer reset` : clear PHP cache, drop database, run migrations, load data and fixtures, and create a new Daedalus. Use this for a fresh start
- `composer load-data` : load config data. Use this for example if you added a new action.
- `composer fill-daedalus` : fill an available Daedalus with all 16 characters.
- `composer lint` : run the linters CSFixer and Psalm
- `composer test` : run all the tests (unit, functional, API)
- `composer diff` : create a migration from the difference between the database and the entities
- `composer update-schema` : update the database schema using the migrations

Please consult the [composer.json](./composer.json) for more commands.

## Tests Codeception
The test folder is a mirror of the src directory
You can Mock classes/services with [Mockery](https://github.com/mockery/mockery)

You can run a unit test with
```
php vendor/bin/codecept run
```

##Xdebug

### Phpstorm
Ensure you have the following configuration
![alt text](./docs/xdebug_phpstorm_debug.png)
![alt text](./docs/xdebug_phpstorm_server.png)

#### Command line:
Prefix your command line with: `XDEBUG_CONFIG="idekey=PHPSTORM"`  
example: `XDEBUG_CONFIG="idekey=PHPSTORM" vendor/bin/codecept run`

#### Request
Add in the query parameter `XDEBUG_SESSION_START=PHPSTORM`  
Example: http://localhost:8080/api/v1/player/1/action?XDEBUG_SESSION_START=PHPSTORM

Troobleshoting: 

#### Ubuntu timeout
Context: running the command on docker ubuntu 20 and getting this error:
`Xdebug: [Step Debug] Time-out connecting to debugging client, waited: 200 ms. Tried: 172.17.0.1:9003 (through xdebug.client_host/xdebug.client_port) :-(`

Check what the folowing command returns:
`sudo ufw status verbose`

On the host machine (not the docker container) run :
`sudo ufw allow from any to any port 9003 proto tcp`


## Accessing Data Base
From terminal type:
```
docker exec -it mush_database bash
psql --username mysql mush
```
List the tables with:
```
\dt
```
Get a table with:
```
select * from table;
```