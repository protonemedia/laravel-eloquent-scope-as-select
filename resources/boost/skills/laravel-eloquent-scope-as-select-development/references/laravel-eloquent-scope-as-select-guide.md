# laravel-eloquent-scope-as-select development guide

For full documentation, see the README: https://github.com/protonemedia/laravel-eloquent-scope-as-select#readme

## At a glance
Allows re-using Eloquent query scopes/constraints by adding them as a **subquery/select**.

## Local setup
- Install dependencies: `composer install`
- Keep the dev loop package-focused (avoid adding app-only scaffolding).

## Testing
- Run: `composer test` (preferred) or the repository’s configured test runner.
- Add regression tests for bug fixes.

## Notes & conventions
- Subquery correctness is essential (bindings, aliases, SQL portability).
- Add tests for complex scopes (whereHas, joins, bindings).
- Prefer database-agnostic SQL when possible.
