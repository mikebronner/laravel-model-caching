# Commit Conventions

## Format

[Conventional Commits](https://www.conventionalcommits.org) + [Gitmoji](https://gitmoji.dev) prefix.

```
<type>(<optional scope>): <gitmoji> <description>.

<optional body>

<optional footer(s)>
```

### Rules

- Description MUST begin with gitmoji + space
- Description MUST end with period
- ONE type and ONE description per commit
- Only include issue refs for REAL GitHub issues

### Example

```
feat(leads): ✨ Add email validation endpoint.

Fixes: #123
```

## Types

| Type | Description |
|------|-------------|
| `feat` | New feature |
| `fix` | Bug fix |
| `docs` | Documentation only |
| `style` | Formatting (no code change) |
| `refactor` | Neither fix nor feature |
| `perf` | Performance improvement |
| `test` | Adding/correcting tests |
| `build` | Build system or dependencies |
| `ci` | CI configuration |
| `chore` | Other non-src/test changes |
| `revert` | Reverts previous commit |

## Scopes

Scopes only apply to `feat` and `fix` (customer-facing release notes).

**Use scope when:** Internal tooling or technical details customers wouldn't understand.

| Scope | Use For |
|-------|---------|
| `internal` | Internal tooling, technical details |
| `admin` | Admin-only features |

```
feat: ✨ Add password reset functionality.
fix: 🐛 Resolve checkout payment error.
feat(internal): ✨ Add admin debugging tools.
fix(internal): 🐛 Fix null check in PaymentProcessor.
```

## Common Gitmojis

| Emoji | Use Case |
|-------|----------|
| ✨ | Introduce new features |
| 🐛 | Fix a bug |
| 🚑️ | Critical hotfix |
| ♻️ | Refactor code |
| 🔥 | Remove code or files |
| ✅ | Add, update, or pass tests |
| 💄 | Add or update the UI and style files |
| ⬆️ | Upgrade dependencies |
| 🔧 | Add or update configuration files |
| 🗃️ | Perform database related changes |
| 🚩 | Add, update, or remove feature flags |
| 🩹 | Simple fix for a non-critical issue |

For the full list, see [gitmoji.dev](https://gitmoji.dev).

## Issue References

Only include for REAL issues being fixed. Each on its own line:

```
fix(auth): 🐛 Resolve token expiration bug.

Fixes: #789
Fixes: #790
```
