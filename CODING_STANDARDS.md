# Coding Standards – Weblabor Projects

> This is the **single source of truth** for all coding standards across Weblabor projects.
> All AI tools (Claude, Copilot), agents (reviewer, developer), and developers must follow these rules.
>
> When user feedback corrects or adds a standard, update this file — all agents and tools benefit automatically.

---

## Language & Text

- All **code must be written in English**
- **Pull Request comments must be written in Spanish**
- Any **user-facing text in Spanish detected in code must be moved to `lang` files** (keys in English)
- No hardcoded user-facing text in Blade or PHP
- No Spanish text directly in Blade or PHP files

---

## Framework & Helper Preference

- Always prefer **Laravel Collections** and native Laravel helpers over native PHP functions for array manipulation, data transformation, and complex string handling
- If a helper already exists in the project: do not recreate it, do not duplicate its logic inline

---

## Dates, Timezones, and Casts

- All dates must be stored in **UTC**
- All date fields must define a **cast**
- Do not manually convert dates in Livewire
- Do not format dates manually when a cast applies

Casts must be used for: Dates, Booleans, Arrays/JSON, Enums.
Do not replace casts with accessors or manual transformations.

---

## Enums

- Use Enums for state management. Use the project's `IsEnum` trait.
- Enum class names must **not** include the word `Enum` (e.g., `AddonStatus`, not `AddonStatusEnum`)
- Enum case names: **PascalCase** (e.g., `Active`, `Pending`)
- Enum string values: **snake_case** (e.g., `'active'`, `'pending'`)
- Use `$model->status->is('CaseName')` passing the PascalCase case name
- Enum business logic must live inside the Enum
- Do not branch logic based on enum cases inside Models

```php
// Correct
$subAddon->status->is('Active');

// Incorrect
$subAddon->status === AddOnStatus::Active;
```

---

## Models

- Always use `$guarded`, never `$fillable`
- Model internal organization order: **Functions → Scopes → Relationships → Attributes**
- Do not mix responsibilities or add decorative comments
- Accessors: only for presentation, formatting, and read-only transformations

### Rich Domain Models

Domain actions must be exposed through model methods. Livewire components call model methods, not services directly.

Pattern: `Livewire / Controller → Model Method → Service / Job`

```php
// Correct
$user->toggleAddOn($addOn);

// Incorrect
AddOnService::toggle(auth()->user(), $addOn);
```

When a model accumulates 4+ related methods, extract them into a **Trait**.

---

## Livewire Components

### Mandatory Structure Order

1. `use` statements / traits
2. Public properties
3. `mount()`
4. Action methods
5. Internal helpers
6. `render()` **(always last)**

### Property Declarations

- Do not declare types on simple (non-object) properties
- Typed properties only required for Eloquent model objects
- Declare multiple simple properties on one line
- Never initialize string properties to empty string (`''`)

```php
// Correct
public Plan $object;
public $prices = [], $limits = [], $features = [], $newFeature;

// Incorrect
public string $name = '';
public string $description = '';
```

### Direct Model Binding

- Bind fields directly to `$this->object` via `wire:model="object.name"`
- Call `$this->object->save()` — avoid building separate data arrays

### No Spaces Before `if`

Never add a blank line directly before an `if` statement inside a method.

```php
// Correct
$value = $this->getValue();
if (! $value) {
    return;
}

// Incorrect
$value = $this->getValue();

if (! $value) {
    return;
}
```

---

## Observers

Preferred event order: `saving` → `creating/updating` → `created/updated`

If logic always runs on model changes, modifies related data, and must remain centralized → it belongs in an **Observer**.

---

## Traits & Reuse

- Reusable logic must be placed in **Traits**
- No copy/paste logic, no duplication across components

---

## Controllers

- Controllers are not part of the standard architecture
- Use Livewire components instead
- Exception: only when Livewire clearly does not make sense

---

## Code Cleanliness

- No single-use variables (inline them)
- No very small functions called only once that don't improve clarity
- No decorative comments — only comments that add real clarity
- No return type declarations on methods (use PHPDoc if needed)

```php
// Correct
public static function all()
{
    return Plan::all();
}

// Incorrect
public static function all(): Collection
{
    return Plan::all();
}
```

---

## Class Declarations (Namespaces)

- Always declare classes with `use` statements at the top of the file
- Never use fully qualified class names inline
- Group imports from the same namespace: `use App\Models\{User, Plan};`

---

## Routes

- Minimize verbosity — define a base namespace and use folder-based notation
- Do not repeatedly declare full class names
- Never invent route names — verify they exist first
- Laravel Front routes follow: `{prefix}.front.{resource_slug}` (e.g., `admin.front.plans`)
- Laravel Front does NOT generate a named route for show pages — use the `base_url` directly

---

## Blade & Frontend

- Reuse existing Blade components — do not duplicate markup

### `x-select` Options Format

Always use `option-key-value`. Never use `option-label`/`option-value` with manually mapped arrays.

```blade
{{-- Correct --}}
<x-select wire:model="currency" :options="$currencyOptions" option-key-value />
```

---

## Custom Input Components

- **Always reuse existing input components before creating new ones**: `x-date-input`, `x-email-input`, `x-phone-input`, `x-domain-input`
- A custom input component must be **self-contained (isolated)**:
  - `wire:model` is the only required attribute — all other props are optional with sensible defaults
  - The component must work in its simplest form with just `wire:model` and optionally `label`
- Never put input-specific logic inline in a Livewire component when it will be reused in more than one place
- When a custom input needs reactive/dynamic behavior (async validation, pickers, dependent selects), use a **Blade component that internally renders a Livewire sub-component**
  - Pattern: `<x-my-input wire:model="field" />` → `resources/views/components/my-input.blade.php` → `@livewire('inputs.my-input', ...)`
  - Reference implementation: `x-date-input` (`resources/views/components/date-input.blade.php` + `app/Livewire/Inputs/Datetime.php`)

---

## `whereHas` Callback Style

Always use long-form function syntax, never arrow functions.

```php
// Correct
->whereHas('plan', function ($query) {
    return $query->where('slug', 'free');
})

// Incorrect
->whereHas('plan', fn($q) => $q->where('slug', 'free'))
```

---

## Notifications

- Every notification must extend `App\Notifications\Notification`
- Never extend `Illuminate\Notifications\Notification` directly
- Every notification must define: `subject()`, `description()`, `image()`
- Use `php artisan make:notification MyNotification` (the stub already extends the base class)
- Do not force channels manually unless justified

---

## Validation Rules

- Non-nullable DB fields → `required`
- Numeric values → reasonable `max`/`min`
- Strings → length validation
- Validate consistency between database schema and validation rules
- In Laravel or Livewire validation, do not use `messages()` just to restate Laravel's default validation text with a translated field name
- Prefer `validationAttributes()` or `attributes()` to provide the user-facing field label when the default validation message structure is still correct
- Use `messages()` only when the validation copy is genuinely custom and changes the meaning or wording beyond Laravel's standard message

```php
// Correct
protected function validationAttributes()
{
    return [
        'task.name' => __('name'),
        'task.description' => __('description'),
        'task.status' => __('status'),
    ];
}

// Incorrect
protected function messages()
{
    return [
        'task.name.required' => __('The name field is required.'),
        'task.name.string' => __('The name field must be a string.'),
        'task.name.max' => __('The name must not exceed :max characters.'),
    ];
}
```

---

## Migrations

- Never use `->cascadeOnDelete()` in migration foreign key definitions
- Use `->constrained()` or `->nullOnDelete()` when appropriate

---

## CRUD Architecture

Use **Laravel Front** for simple admin CRUDs. Use **Livewire** for create/edit only when:
- The form manages multiple related models inline
- It has dynamic add/remove rows
- It requires real-time field interactions

When Livewire overrides a Front route:
```php
Route::front('Team\\Product');
Route::livewire('/products/create', Livewire\Teams\ProductEdition::class)->name('products.create');
```

---

## Relationship-Based Model Creation

Always use Eloquent relationships to create related models.

```php
// Correct
$plan->limits()->create(['limit_key' => 'activities', 'limit_value' => 100]);

// Incorrect
StripeLimit::create(['limitable_type' => Plan::class, 'limitable_id' => $plan->id, ...]);
```

---

## Domain Logic Reuse (Single Source of Truth)

Each calculation lives in the model that owns the data. Other models and services reuse that method.

```php
// Correct
$total = $project->tasks->sum('cost');

// Incorrect — duplicates the formula
$total = $project->tasks->sum(fn ($task) => $task->hours * $task->rate);
```

---

## Testing

Every new feature **must** include corresponding tests as part of the same task.

### Test Philosophy

Tests must validate **real behavior**, not just config values.

```php
// Bad — only checks a config value
$this->assertFalse(config('plans.enabled'));

// Good — validates the behavior
config(['plans.enabled' => false]);
$user = User::factory()->create();
$this->actingAs($user)->get('/app')->assertStatus(200);
```

### Test Location

| Project | Directory |
|---|---|
| Weblabor Base | `tests/WeblaborBase/` |
| WeblabOR Admin | `tests/WeblaborAdmin/` |
| WeblabOR Teams | `tests/WeblaborTeams/` |

Inside each: `Feature/` for HTTP/Livewire/middleware tests, `Unit/` for traits/enums/models/services.

### Test Isolation

- All tests must use in-memory SQLite (configured in `phpunit.xml`)
- All tests that touch the database must use `RefreshDatabase` trait
- Never remove `force="true"` attributes from `phpunit.xml`

---

## Documentation

Any new feature, config option, or architectural component **must** include documentation in `/docs/{project}`.

- All docs in `/docs/{project-name}` using **kebab-case** folder names
- Each project folder must have a `README.md` linking every `.md` file
- New `.md` files must be linked from the project `README.md`
- Content: practical and usage-focused — never include database schemas or migration details
- If a folder in `/docs` does NOT match the current project → ignore it completely

### What Requires Documentation

- New Livewire components with non-obvious behavior
- New `config/` keys → update `docs/{project}/configuration.md`
- New custom Artisan commands → update `docs/{project}/development.md`
- New services, traits, helpers created for reuse
- New Blade components created in this project → update `docs/{project}/ui-components.md`
- New environment variables introduced by this project
- Architectural decisions

---

## Dead Code & No-Impact Changes

Flag explicitly:
- New files not referenced anywhere
- Classes never used
- Logic that never executes
- Changes that do not alter behavior
