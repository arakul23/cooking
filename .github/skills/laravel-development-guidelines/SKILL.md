---
name: laravel-development-guidelines
description: Laravel development rules, including mandatory FormRequest validation.
---

# Laravel Development Guidelines

## Request Validation
- **Strict Default (Always FormRequest):** ALWAYS extract HTTP request validation into dedicated `FormRequest` classes in `app/Http/Requests/`. Do this for ALL endpoints—including `GET` routes and requests with even a single parameter. Type-hint the `FormRequest` in the controller signature and access data strictly via `$request->validated()`.
- **No `$request->validate()`:** Never use `$request->validate()` in controller methods.
- **Rare Exceptions for `Validator::make()`:** You may use the `Validator` facade (`Validator::make()`) inside a controller ONLY when a `FormRequest` cannot be cleanly applied:
  1. Validating internal/non-request data (e.g., an array parsed from a file, external API response, or background payload).
  2. When validation rules depend on complex database state or business logic resolved midway through the controller action.
  3. When custom failure handling is required instead of throwing a standard `ValidationException` (e.g., logging failure details and returning a custom response without failing the request).
