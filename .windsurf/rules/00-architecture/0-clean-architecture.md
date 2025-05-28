---
trigger: model_decision
description: APPLY Clean Architecture principles WHEN organizing code in backend
globs: **/*.php
---

# Layers Overview

- Split into: **Entity/Domain**, **Repository**, **Service**, **Controller**
- Follow Dependency Inversion: `Service → Interface → Repository`
- Domain and Service layers remains framework-agnostic

# Layer Structure

- **Entity / Domain Layer**:
  - Contains business models, enums, value objects
  - Place business logic and entities here.
  - Define domain services for complex logic.

- **Service Layer**:
  - Implement use cases as orchestrators.
  - Services are signle-responsability commands : `LoginUserService` instead of `UserService`.
  - Validate input at boundaries.

- **Infrastructure / Repository Layer**:
  - Implement domain repository interfaces.
  - Isolate external systems (DB, APIs, files, random, time).
  - Keep infrastructure out of business logic.

- **Presentation / Controller Layer**:
  - Handle API requests and responses.
  - Centralize error handling and validation
  - Delegate business logic to services.

# Critical Rules

- Keep outer layers from depending on inner layers.
- Define repository interfaces in the domain layer.
- Avoid mixing business logic with infrastructure logic.