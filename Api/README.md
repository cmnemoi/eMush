# Architecture (Outdated, to be updated for Symfony)

## Directory Tree:
  
    |-- build/              --> Compiled Js (not commited)
    |-- config/             --> Game configs
    |-- docs/               --> Documentation (Readme)
    |-- locales/            --> Translations
    |-- logs/               --> Logs generated (not commited)
    |-- migration/          --> Migrations
    |-- node_modules/       --> A black hole (not commited)
    |-- src/            
        |-- actions/        --> All the implementation of an action
        |-- config/         --> Devlopement config (database, routes, etc..)
        |-- controllers/    --> controllers
        |-- enums/          --> All the enums
        |-- events/         --> The event handlers
        |-- models/         --> Entities, the core entities
        |-- repository/     --> The layer between the ORM and the application
        |-- security/       --> Authentification handling
        |-- services/       --> Business logic
        |-- index.ts        --> entrypoint
    |-- tests/              --> Test directory
    |-- .env                --> environment variables
    |-- package.json        --> dependencies

## Core concepts:

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
- Create a class that extends [src/actions/action.ts](../src/actions/action.ts) abstract class: implement the abstract methods
- Register this action in the [src/enums/actions.enums.ts](../src/enums/actions.enum.ts)
- Add the new Class in the [src/actions/list.actions.ts](../src/actions/list.action.ts)
#### Event
Trigger an event:
```
eventManager.emit(EventName, ...parameters)
```
Implement the action handler in events/

## New Models
To create a new Model (persisted):
- Add you model class in src/models
- register you model in [src/config/database.ts](../src/config/database.ts)
- Create the migration run in the container:  
```
npm run generate-migration
```
To apply the migration:
```
npm run compile && npm run run-migration
```

## RoomLogs
When an action or an event is performed, a roomLog should be created  
In order to add a roomLog, use the RoomLogService::createLog method
```
RoomLogService.createLog(LogEnum.EAT,{character: this.player.character},this.player.room,this.player,VisibilityEnum.SECRET);
```
To crate a new Log: 
- Add the log to the [log.enum.ts](../src/enums/log.enum.ts)  
- Add the log in the [locales](../locales/fr/logs.ts)

## Random value
Whenever you need to generate a random value, use the [RandomService](../src/services/random.service.ts)
```
public static random(nbValuePossible = 100): number {
```

## Tests
Please create a test for every new created functionality  
The test folder is a mirror of the src directory

## Main dependencies

[Express4](https://expressjs.com/) Fast, unopinionated, minimalist web framework for Node.js 

[TypeScript](https://www.typescriptlang.org/) extends JavaScript by adding types.

[TypeORM](https://typeorm.io/#/) is an ORM that can run in NodeJS, Browser, Cordova, PhoneGap, Ionic, React Native, NativeScript, Expo, and Electron platforms and can be used with TypeScript and JavaScript (ES5, ES6, ES7, ES8)

[Passport](http://www.passportjs.org/) Simple, unobtrusive authentication for Node.js

[winston](https://github.com/winstonjs/winston) A logger for just about everything.

[Mocha](https://mochajs.org/) is a feature-rich JavaScript test framework running on Node.js and in the browser, making asynchronous testing simple and fung.

[Chai](https://www.chaijs.com/) is a BDD / TDD assertion library for node and the browser that can be delightfully paired with any javascript testing framework.