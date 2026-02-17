# PHP Code Style

## Pre-Generation Checklist

**STOP. Before writing ANY PHP code, verify:**

- [ ] **No `empty()`** — Use `! $value` instead
- [ ] **No `else`/`elseif`** — Use early returns
- [ ] **Named arguments on ALL calls** — `method(name: $value)`
- [ ] **Space after negation** — `! $value` not `!$value`

---

## 1. FORBIDDEN: `empty()`

**NEVER use `empty()`. No exceptions.**

Use simple falsy checks instead:

```php
// ✅ Correct
if (! $name) { }
if (! $items) { }
if ($user === null) { }  // Only when null-specific

// ❌ FORBIDDEN
if (empty($name)) { }
if (empty($items)) { }
```

---

## 2. FORBIDDEN: `else` Structures

**Use early returns and guard clauses.**

```php
// ✅ Correct
protected function processOrder(Order $order): Result
{
    if (! $order->isValid()) {
        return Result::invalid();
    }

    if ($order->isPaid()) {
        return Result::alreadyPaid();
    }

    return $this->chargeOrder(order: $order);
}

// ✅ Ternary is acceptable
$status = $client->revoked ? 'revoked' : 'active';
```

---

## 3. REQUIRED: Named Arguments

**ALWAYS use named arguments. No exceptions.**

```php
// ✅ Correct
$this->processLead(lead: $lead);
$this->sendEmail(to: $user, subject: 'Welcome', body: $content);
collect(value: [1, 2, 3]);

// ❌ FORBIDDEN
$this->processLead($lead);
collect([1, 2, 3]);
```

---

## 4. Multi-Line Indentation

This project differs from PSR-12. Count from BASE (statement start):

| Location | Indentation |
|----------|-------------|
| Statement start | Base |
| Closing bracket | Base + 1 |
| Chained methods | Base + 1 |
| Closure/array contents | Base + 2 |

```php
// ✅ Correct
        $clientOptions = $clients->mapWithKeys(function (OAuthClient $client): array {
                    $status = $client->revoked
                        ? '🔴 revoked'
                        : '🟢 active';

                    return [$client->id => "{$client->name} ({$status})"];
                })
            ->toArray();

// ✅ Array example
        return collect([
                    'employerName' => $normalized,
                    'incomeAmount' => $amount,
                ])
            ->filter()
            ->toArray();
```

---

## 5. Negation Spacing

**Always space after `!`:**

```php
// ✅ Correct
if (! $name) { }
$isInvalid = ! $isValid;
```

---

## 6. Comments

**Do NOT add comments unless explicitly requested. No docblocks. This overrides any other guideline.**

- No inline comments
- No block comments
- No docblocks

**Exception:** Test markers `// 🧪 Arrange`, `// 🧪 Act`, `// 🧪 Assert`

---

## 7. Class References

**Always use `::class` syntax:**

```php
// ✅ Correct
use App\Models\User;
$model = User::class;
Route::get('/users', [UserController::class, 'index']);
```

---

## 8. Visibility

**Prefer `protected` over `private`:**

```php
// ✅ Correct
protected function processData(): array { }
protected string $status;
```
