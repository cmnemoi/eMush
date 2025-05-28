---
trigger: model_decision
description: Apply Vue.js 3 best practices for component structure, state management, options API, and testability in Vue components. Ensures maintainability, performance, and separation of concerns across the application.
globs: App/src/**/*.vue
---

Component Structure:
- Use Composition API for new components.
- Keep components focused on a single responsibility.
- Extract reusable logic into composables.
- Strongly type component props with `defineProps<{...}>()`.
- Provide default values with `withDefaults()`.
- Use `<script setup>` for simpler component definitions.
- Use Options API exclusively.

Reactivity:
- Use `ref()` for primitive values.
- Use `reactive()` for object values.
- Prefer `computed()` over methods for derived state.
- Use `watch()` and `watchEffect()` sparingly.
- Extract complex reactive logic into composables.
- Avoid direct mutation of props.
- Use `toRefs()` when destructuring reactive objects.

State Management:
- Use a global state management library.
- Create stores based on domains, not components.
- Implement dependency injection for service access.
- Define store interfaces for better testability.
- Keep component state local when possible.
- Use `provide`/`inject` for deep component trees.
- Avoid direct store imports in components.

Templates:
- Use `v-bind` shorthand (`:prop`) for dynamic props.
- Use `v-on` shorthand (`@event`) for event handlers.
- Avoid complex expressions in templates.
- Extract complex logic to computed properties.
- Use template refs instead of querying DOM.
- Implement slots for component composition.
- Use teleport for modals and popups.

Performance:
- Apply `v-memo` for expensive renders.
- Use `v-once` for static content.
- Implement `defineAsyncComponent()` for code splitting.
- Add `v-pre` for non-reactive content.
- Use `shallowRef()` for large objects with no deep reactivity.
- Apply `markRaw()` for non-reactive objects.
- Implement virtual scrolling for large lists.

Ports & Adapters:
- Define service interfaces in `types` directory.
- Implement concrete services in `services` directory.
- Use dependency injection for service access.
- Mock services in tests with interfaces.
- Extract API calls from components and stores.
- Create repository pattern for data access.
- Separate domain models from API responses.

Testing:
- Write unit tests for composables.
- Test components with Vue Test Utils.
- Replace external dependencies by test doubles (fakes, stubs).
- Test one concern per test.
- Use component testing library for integration tests.
- Apply test-driven development for complex logic.
- Create test factories for common test data.