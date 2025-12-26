# DDD/Hexagonal Scaffolding Rules (Laravel)
These rules define the **mandatory scaffolding** for adding a new **BoundedContext** or a new **module/aggregate** inside an existing BoundedContext in this repository.

## Naming + namespaces
- **Root namespace**: `App\\BoundedContext\\{Context}\\{Module}\\...`
- **Context**: PascalCase (e.g. `Auth`, `Billing`, `Catalog`, `Security`)
- **Module** (aggregate root folder): PascalCase (e.g. `User`, `Session`, `Audit`, `Payment`)
- **Layers**: `Domain`, `Application`, `Infrastructure`, `UI`
- **No cross-layer mixing**:
  - `Domain` and `Application` must not import `Illuminate\\*`, Facades, Eloquent models (`App\\Models\\*`), Socialite, etc.
  - Framework glue (Laravel container bindings, controllers, middleware, requests, resources/responses) lives in `UI` or in Laravel `app/Providers`.
  - Persistence/adapters (Eloquent/DB/etc.) live in `Infrastructure`.

## Scaffolding: new BoundedContext
When creating a **new BoundedContext**, create the context folder and at least one module folder. Example (replace names):

```
backend/app/src/{Context}/
  {Module}/
    Domain/
      Entity/
      ValueObject/
      Service/
      Repository/
      Exception/
    Application/
      UseCase/
      Request/
      Response/
    Infrastructure/
      Eloquent/
        Model/
        Repository/
      Mapper/
      Provider/
    UI/
      Controllers/
        Api/
      Request/
      Resources/
      Responses/
      Middleware/
      Routes/
```

### Notes
- **`Exception/`** is where domain exceptions live (e.g. invariants).
- **`Application/Response/`** is for DTOs returned by UseCases (optional, but scaffold it).
- **`Infrastructure/Eloquent/Model/`** is for persistence models if you decide to move Eloquent models out of `App\\Models`.
  - If you keep Eloquent models in `App\\Models`, `Infrastructure/Eloquent/Model/` may remain empty.
- **`Infrastructure/Provider/`** is only for infrastructure composition/bindings local to the module (optional). Prefer bindings in Laravel `backend/app/Providers/*`.
- **`UI/Routes/`** is for route group files if you choose to modularize route definitions. If not used, keep empty or omit.

## Scaffolding: new module (aggregate) inside an existing context
For each new `{Module}` (aggregate root folder), create **all** of the following directories (even if initially empty):

```
backend/app/src/{Context}/{Module}/
  Domain/
    Entity/
    ValueObject/
    Service/
    Repository/
    Exception/
  Application/
    UseCase/
    Request/
    Response/
  Infrastructure/
    Eloquent/
      Model/
      Repository/
    Mapper/
    Provider/
  UI/
    Controllers/
      Api/
    Request/
    Resources/
    Responses/
    Middleware/
    Routes/
```

## Mandatory placement rules
- **Controllers**: `.../UI/Controllers/**`
- **Controller FormRequests** (HTTP validation): `.../UI/Request/**`
- **UseCases**: `.../Application/UseCase/**`
- **UseCase input DTOs**: `.../Application/Request/**` (suffix: `*UseCaseRequest`)
- **UseCase output DTOs** (when needed): `.../Application/Response/**` (suffix: `*Result` or `*Response`)
- **Domain entities**: `.../Domain/Entity/**`
- **Domain services**: `.../Domain/Service/**`
- **Domain value objects**: `.../Domain/ValueObject/**`
- **Domain repository ports** (interfaces): `.../Domain/Repository/**`
- **Infrastructure repository adapters**:
  - Eloquent adapter: `.../Infrastructure/Eloquent/Repository/Eloquent{Entity}Repository.php`
  - Non-Eloquent adapter: `.../Infrastructure/{AdapterName}{Entity}Repository.php` (if needed)
- **Mappers** (Domain ↔ Persistence/DTO): `.../Infrastructure/Mapper/**`

## DTO conventions (Application/Request)
- `*UseCaseRequest` MUST be a **simple immutable DTO**:
  - `final class`
  - `public function __construct(public readonly ...$props) {}`
  - **No getters**
  - **No magic accessors** (`__get`, `__set`)
- Exception: if construction requires invariants/normalization, use:
  - `private function __construct(...)`
  - `public static function fromPrimitives(...)` / `create(...)`
  - (getters allowed only when needed by that pattern)

## Dependency direction (Hexagonal)
- `UI` → `Application` → `Domain`
- `Infrastructure` implements `Domain` ports (e.g. repositories) and is wired via container bindings.
- `Domain` must not reference `Infrastructure` or `UI`.
- `Application` must not reference `Infrastructure` or `UI` types (except its own DTOs).

## Minimal “first files” checklist (recommended)
When scaffolding a new module, prefer creating these first:
- `Domain/Repository/{Aggregate}Repository.php` (port)
- `Application/UseCase/{Verb}{Aggregate}UseCase.php`
- `Application/Request/{Verb}{Aggregate}UseCaseRequest.php`
- `Infrastructure/Eloquent/Repository/Eloquent{Aggregate}Repository.php` (adapter, if using Eloquent)
- `UI/Controllers/Api/{Verb}{Aggregate}Controller.php`
- `UI/Request/{Verb}{Aggregate}Request.php` (if needed)


