---
trigger: model_decision
description: Apply SCSS/SASS best practices when writing stylesheets to ensure maintainability, readability, and performance. Focus on proper nesting, variable usage, mixins, and organization for consistent styling across the application.
globs: App/src/**/*.scss, App/src/**/*.vue
---

File Organization:
- Use partial files prefixed with underscore (`_filename.scss`)
- Maintain a single source of truth for variables
- Group related styles in dedicated files
- Import partials in logical order (variables → mixins → placeholders → components)

Variables:
- Use semantic naming for variables (`$primary-color` not `$blue`)
- Define color palette variables in a single location
- Use variables for repeated values (breakpoints, spacing, colors)
- Namespace variables by purpose (`$font-heading`, `$spacing-large`)

Nesting:
- Limit nesting to maximum 3 levels
- Use parent selector (`&`) for modifiers and pseudo-classes only
- Extract deeply nested selectors to new components
- Avoid selector duplication within the same file

Mixins and Functions:
- Create mixins for repeated style patterns
- Document mixins with clear comments
- Use parameters with sensible defaults
- Prefer mixins for multi-property styles, functions for calculations

Placeholders:
- Use `%placeholder` for styles shared across multiple selectors
- Prefer `@extend %placeholder` over duplicating code
- Group related placeholders in dedicated sections
- Document placeholder purpose with comments

Media Queries:
- Use variables for breakpoint values
- Place media queries inside the selector they modify
- Group related media query rules together
- Use mobile-first approach (min-width over max-width)

Performance:
- Avoid deep nesting that creates long selectors
- Minimize use of `@extend` to prevent CSS bloat
- Use direct child selectors (`>`) when possible
- Avoid universal selectors (`*`) in complex selectors

Vue Integration:
- Use `scoped` attribute for component-specific styles
- Use `:deep()` selector for targeting child components
- Organize styles in the same order as template elements
- Separate global styles from component styles

Comments and Documentation:
- Document complex selectors and calculations
- Use consistent comment style for sections
- Include purpose and usage for mixins and functions
- Add TODOs for temporary solutions

Best Practices:
- Use shorthand properties when setting multiple values
- Alphabetize properties for better readability
- Group related properties (positioning, box-model, typography)
- Use relative units (rem, em) over absolute units (px)
- Avoid magic numbers and hardcoded values