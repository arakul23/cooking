# Project Conventions

## Change process
- Before making any change, briefly explain what you are going to do and why, including any alternative approaches you considered.
- For changes touching more than one file, or changes that affect API behavior, describe the plan first and wait for confirmation before writing code.
- Never delete or rename public methods, routes, or database columns without explicit confirmation — this can break other parts of the system.
- Do not modify migrations, config files (.env, config/*.php), or composer dependencies unless explicitly asked to.
- Follow existing patterns already used in the project (repositories, services, form requests, resource classes). Do not introduce a new architectural approach unless explicitly requested.
- If a request is ambiguous or could be implemented in more than one reasonable way, ask before proceeding rather than guessing.
- After making a change, summarize what was changed and why in plain language.

## Language and environment
- PHP, strict typing (`declare(strict_types=1)` in new files).
- Laravel backend, Filament for admin panels, Nuxt.js/Vue.js frontend.
- MySQL for persistence, Redis for caching/queues.

## Code style
- Follow PSR-12.
- Use Eloquent for database access; avoid raw SQL unless there's a clear performance reason, and explain that reason if used.
- Prefer explicit return types and typed properties over relying on inference.
- Keep controllers thin — business logic belongs in services or actions, not controllers.
- Use Form Requests for validation rather than inline validation in controllers.

## Filament best practices
- Use Filament's built-in Resource, RelationManager, and Page classes instead of building custom CRUD screens from scratch.
- Put form and table field definitions inside the Resource's `form()` and `table()` methods; avoid duplicating field logic across multiple files.
- Use Filament Form/Table Builder components (e.g. `TextInput`, `Select`, `Repeater`) rather than raw Blade/HTML inputs inside panels.
- Reuse existing Filament Resources' patterns (e.g. how relationships, media, and validation are handled) instead of inventing a new structure per resource.
- Put reusable field configuration or table columns into traits or dedicated classes when the same fields are shared across multiple Resources.
- Use Filament policies/authorization (`can()`, Resource-level `shouldRegisterNavigation()`, etc.) instead of manual permission checks inside pages.
- Keep custom Filament Pages/Widgets thin — push business logic into services, not into the Livewire component itself.

## Nuxt.js / Vue best practices
- Use the Composition API with `<script setup>` for new components; do not mix in Options API unless the file already uses it.
- Use Nuxt's auto-imports (composables, components) instead of manually importing things Nuxt already provides.
- Put data-fetching logic in composables (`useFetch`, `useAsyncData`, or custom `useXyz` composables) rather than directly in component `<script>` blocks when it's reused across pages.
- Keep components small and focused; extract repeated markup/logic into reusable components or composables rather than duplicating it.
- Use Pinia for shared/global state; avoid prop-drilling across many component levels or ad-hoc global reactive objects.
- Respect Nuxt's file-based routing and directory structure (`pages/`, `layouts/`, `components/`, `composables/`, `server/`) — don't introduce a different folder convention.
- Type props, emits, and composable return values with TypeScript where the project already uses TS.
- Avoid direct DOM manipulation; prefer Vue's reactivity and template bindings.

## Testing and safety
- Run existing tests/linters after making a change, when available.
- Do not remove or weaken existing tests to make them pass.
- Flag any change that could have side effects on unrelated features (e.g. shared services, global config, queued jobs).