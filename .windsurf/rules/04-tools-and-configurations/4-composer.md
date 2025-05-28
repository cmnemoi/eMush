---
trigger: model_decision
description: Apply Composer best practices WHEN managing PHP dependencies to ensure maintainability, performance, and proper package management across the application
globs: Api/**/*.php
---

Package Management:
- Use `composer require` for adding new dependencies
- Use `composer remove` for removing dependencies
- Specify exact version constraints for critical dependencies
- Add extensions with `ext-*` prefix in require section
- Group packages by purpose in require/require-dev sections

Dependency Configuration:
- Set `optimize-autoloader: true` for production
- Configure `preferred-install` as `dist` for faster installs
- Enable `sort-packages: true` for consistent ordering
- Explicitly configure plugin permissions in `allow-plugins`
- Replace polyfills with `replace` section when using newer PHP

Autoloading:
- Follow PSR-4 standard for namespace autoloading
- Use `classmap` only for legacy code without namespaces
- Define separate autoloading for tests in `autoload-dev`
- Keep namespace structure matching directory structure

Scripts:
- Create custom scripts for repetitive tasks
- Group related commands with script arrays
- Use `@` notation to reference other scripts
- Create namespaced scripts for domain-specific operations
- Define post-install/update hooks for automatic setup
- Add test scripts for specific modules/components
- Create linting scripts that run all code quality tools