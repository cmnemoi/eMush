---
trigger: model_decision
description: APPLY feature based architecture WHEN organizing code in frontend
globs: apps/frontend/**
---

# Feature Organization

* Code organized by business domain
* Features are self-contained
* Shared components in a central library
* Communication through defined interfaces (e.g., store management library)

# Component Structure

* Follow front-end framework recommended template/script/style split
* Typed components (TypeScript)
* Each component has a single responsibility

# State Management

* Store management library (Redux, Vuex, Pinia) for global state
* Isolate state per feature
