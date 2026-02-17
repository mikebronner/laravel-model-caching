# Database Conventions

## Foreign Keys

- Do NOT add `->cascadeOnDelete()` or `->cascadeOnUpdate()` to foreign key constraints.
- Our team intentionally avoids cascading deletes/updates for data integrity and explicit control.
- Foreign keys should use `->constrained()` or explicit `->references()->on()` without cascade modifiers.
