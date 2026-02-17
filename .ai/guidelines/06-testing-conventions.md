# Testing Conventions

## Framework

Use Pest with expressive syntax over PHPUnit assertions.

## Running Tests

```bash
php artisan test --compact
php artisan test --compact --filter=TestName
```

## Test Types

| Type | Purpose | Speed | External Services |
|------|---------|-------|-------------------|
| Unit | Individual classes/methods | Fastest | Mocked |
| Feature | Application features e2e | Fast | Mocked |
| Integration | External service interaction | Slower | Real |
| Architecture | Code structure enforcement | Fast | None |

Use feature tests for HTTP endpoints and integration flows. Use unit tests for isolated logic and value objects.

## Test Structure (REQUIRED)

All tests MUST use AAA pattern with section markers:

```php
it('creates a new lead', function () {
    // 🧪 Arrange
    $payload = ['email' => 'test@example.com'];

    // 🧪 Act
    $response = $this->postJson('/api/leads', $payload);

    // 🧪 Assert
    $response->assertStatus(201);
});
```

**Rules:**
- All three sections required, even if minimal
- Assertions ONLY in Assert section
- This overrides the "no comments" rule

## Datasets

Use for testing same logic with multiple inputs:

```php
it('validates email formats', function (string $email, bool $valid) {
    // 🧪 Arrange
    $validator = new EmailValidator();

    // 🧪 Act
    $result = $validator->isValid($email);

    // 🧪 Assert
    expect($result)->toBe($valid);
})->with([
    'valid email' => ['user@example.com', true],
    'missing @' => ['userexample.com', false],
    'missing domain' => ['user@', false],
]);
```

## Best Practices

- Test behavior/outcomes, not implementation
- Tests must be independent and order-agnostic
- Maintain 80%+ coverage
- Use `beforeEach()` for setup, `afterEach()` for cleanup
- Test edge cases: nulls, empty values, boundaries, errors
- Mock external services in unit/feature tests
- Use `describe()` to group related tests

## Anti-Patterns

- Testing private methods directly
- Ignoring flaky tests
- Skipping "simple CRUD" tests
- Setup > 10 lines (extract to helpers/traits)
