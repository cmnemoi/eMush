# mush-front

## Project setup

### App setup

See the main README for how to make the app run on localhost from a container.

### Linter setup

Run the command:

```bash
yarn install
```

It should install both eslint and stylelint for this project, making sure you automatically follow our style rules without even thinking about it. To get the best out of the linters, make sure your IDE supports them or has an extension for them.

Currently, any error found by the linter will make the pipeline fail when opening a Merge Request, but warning aren't blocking. Most of the rules are set at the warning level to be unintrusive. If any rule feels more annoying than helpful, feel free to update the linter config accordingly.

_Ex: with VScode, you might want to install both the [eslint](https://github.com/Microsoft/vscode-eslint) and [stylelint](https://github.com/stylelint/vscode-stylelint) extensions._

## Project structure

### src/components

This is the core of what the App displays, and contains all the .vue files that make the app work. This is where the main changes happen when implementing new features.

If you're not already familiar with Vue and how a component-based framework works, it is highly recommended you follow the [Vue guide](https://vuejs.org/v2/guide/).

### src/entities

This helps us introduce a form of type verification on the frontend.

_Ex: the entity Channel is a class that represents a communication channel. It has participants, a scope and an id._

### src/enums

This helps us handle any list of possible values for any field. It's mostly used to manage assets, so we can link for example a status name to its corresponding icon.

### src/router

This handles the routing system. It's currently very basic, as the App doesn't have many pages.

### src/services

This handles all calls to the backend API. It's where we make sure the right route is called with the right params. It can also be where we start sanitizing the response of an API call.

_Ex: casting the returned channels as Channel objects_

### src/store

This is where we interact with the values that are stored globally in the App, which means almost all of them.

_Ex: the messages from the communication channels are stored there while a tab is closed_

Each module has the following 4 subcomponents:

#### 1. State

It's where we store the actual data. You can think of it as the "Data" part in a Vue component.

#### 2. Getters

It's where we store computed data. You can think of it as the "computed" part in a Vue component.

_Ex: if we have a `currentChannel` and `messagesByChannelId` in the state, it's going to be annoying to always call `messagesByChannelId[currentChannel.id]`. Instead, you can set a getter and call it `messages` for a better access._

Note: you can also do getter functions. _Ex:_

```js
getMessages: (state) => (channel) => {
    return state.messagesByChannelId[currentChannel.id];
}
```

#### 3. Actions

It's how we interact with the store to change its value. You can think of it as the "method" part in a Vue component.

To call an action from another action, you must use the `dispatch` keyword.
To mutate the state from an action, you must use the `commit` keyword.

Feel free to take inspiration from the existing actions.

#### 4. Mutations

All mutations of the state must go through these methods, as the state cannot be mutated directly. Each mutation method should tell explicitely what it does.

_Ex: `setLoading`, `setCurrentChannel`, `resetError`_

### src/utils

This folder contains plain js files with helper functions. Each function should be stored in a separated file, and ideally come with its test file named myFunction.test.js.

For testing, we use chai (syntax) + mocha (framework) + sinon (stubbing and spying). You can run all tests with the command:

```bash
yarn test
```

## Contributing


To start working on something / implement a new feature:

- Find the appropriate card in the git issue list. Make sure it's not already assigned to another developer. If the issue doesn't exist, feel free to create it
- Make sure no one else is already working on it (the issue should be assigned)
- Assign yourself to the issue.
- Code your feature
- Once done, open a MR on Gitlab with the 'Front' label
- Add at least one reviewer, you can ask for volunteers on the Discord or assign any dev you know can review
- Once it's approved, congrats! You can merge your code, then move the card to the "Done" list and start a new one

If you want to use a different process, feel free to do so and say so on Discord.
