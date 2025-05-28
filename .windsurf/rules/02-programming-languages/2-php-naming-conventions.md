---
trigger: model_decision
description: APPLY PHP naming conventions WHEN writing PHP code to ensure consistency, readability, and maintainability across the codebase
globs: **/*.php
---

Classes:
- Use PascalCase for class names
- Use nouns or noun phrases

Interfaces and Traits:
- Use PascalCase for names
- Suffix interfaces with `Interface`
- Suffix traits with `Trait`

Methods and Functions:
- Use camelCase for names
- Use verbs for actions
- Use nouns for value-returning methods
- Prefix boolean-returning methods with `is`, `has`, or `should`
- Use type declarations for parameters and return types

Properties and Variables:
- Use camelCase for names
- Declare visibility explicitly (`private`, `protected`, `public`)
- Use type declarations for properties
- Use constructor property promotion for dependency injection

Constants and Enums:
- Use UPPER_SNAKE_CASE for enum cases
- Group related constants in enum classes with descriptive names

Namespaces:
- Use PascalCase for namespace segments
- Organize by domain concepts
- Follow PSR-4 autoloading standard

File Names:
- Match class name exactly including case
- One class per file