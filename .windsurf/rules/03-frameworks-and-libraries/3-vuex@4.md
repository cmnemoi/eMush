---
trigger: model_decision
description: APPLY Vuex 4 state management patterns WHEN managing application state to ensure testability, maintainability, and proper separation of concerns in Vue applications
globs: App/src/store/**/*.ts
---

Store Organization:
- Organize modules by domain feature
- Use namespaced modules only
- Define strong TypeScript interfaces
- Extract service interfaces
- Separate state initialization
- Limit module dependencies

State and Mutations:
- Define initial state as constants
- Use TypeScript for state typing
- Create single-purpose mutations
- Keep mutations synchronous only
- Avoid direct state mutation
- Implement immutable state updates

Actions:
- Inject dependencies via parameters
- Handle all async operations
- Use try/catch for error handling
- Dispatch related actions
- Commit only via mutations
- Return meaningful values

Getters:
- Type all getter return values
- Create derived state in getters
- Keep getters pure functions
- Avoid complex calculations
- Compose getters for complex data
- Cache expensive computations

Side Effects:
- Isolate API calls in services
- Use dependency injection pattern
- Create service interfaces
- Replace API calls in tests by test doubles (fakes)
- Handle errors consistently
- Log errors with context

Component Integration:
- Use mapGetters for computed properties
- Use mapActions for methods
- Avoid direct $store access
- Destructure mapped properties
- Prefer Options API helpers
- Test component-store integration

Testing Stores:
- Unit test each module separately
- Replace external dependencies by test doubles (fakes, stubs)
- Test mutations in isolation
- Test action flows completely
- Verify state transitions
- Use typed test fixtures