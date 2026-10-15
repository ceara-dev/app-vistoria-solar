# 📐 Padrão de Arquitetura — Laravel

> Documento normativo de arquitetura para projetos Laravel.
> **Obrigatório** para todos os desenvolvedores do time.

---

## 📑 Índice

1. [Visão Geral](#-visão-geral)
2. [Estrutura de Pastas](#-estrutura-de-pastas)
3. [Camadas e Responsabilidades](#-camadas-e-responsabilidades)
4. [Fluxo de uma Requisição](#-fluxo-de-uma-requisição)
5. [Regras por Camada](#-regras-por-camada)
6. [Exemplos de Código](#-exemplos-de-código)
7. [Regras Proibidas (Anti-patterns)](#-regras-proibidas-anti-patterns)
8. [Checklist de Pull Request](#-checklist-de-pull-request)
9. [Convenções de Nomenclatura](#-convenções-de-nomenclatura)

---

## 🎯 Visão Geral

Este projeto segue uma arquitetura em camadas inspirada em **Clean Architecture** e **DDD** (Domain-Driven Design), adaptada para o ecossistema Laravel.

### Princípios Fundamentais

- **Separação de responsabilidades** — cada camada tem um papel único
- **Injeção de dependência** — via construtor, nunca instanciação direta
- **Controller fino** — apenas orquestra, nunca contém lógica
- **Service isolado por Controller** — 1 Controller → 1 Service
- **Validação isolada** — sempre via `FormRequest`
- **Resposta padronizada** — sempre via `JsonResource`

### Stack de Camadas

```
Request → Controller → Service → Repository → Model
                ↓
            Resource
```

---

## 🗂️ Estrutura de Pastas

```
app/
├── Http/
│   ├── Controllers/
│   │   └── UserController.php
│   ├── Requests/
│   │   └── User/
│   │       ├── StoreUserRequest.php
│   │       └── UpdateUserRequest.php
│   └── Resources/
│       └── UserResource.php
├── Services/
│   └── UserService.php
├── Repositories/
│   ├── Contracts/
│   │   └── UserRepositoryInterface.php
│   └── UserRepository.php
├── Models/
│   └── User.php
└── Providers/
    └── AppServiceProvider.php
```

### Regra de organização

Cada **domínio** possui seus próprios arquivos dentro de cada camada:

- `Requests/User/` — todos os Requests do domínio `User`
- `Services/UserService.php` — Service do domínio `User`
- `Repositories/UserRepository.php` — Repository do domínio `User`

---

## 🧩 Camadas e Responsabilidades

| Camada           | Responsabilidade                                      | Pode acessar                       |
| ---------------- | ----------------------------------------------------- | ---------------------------------- |
| **FormRequest**  | Validar e autorizar entrada                           | Regras de validação                |
| **Controller**   | Orquestrar `Request → Service → Resource`             | Service, Resource                  |
| **Service**      | Regra de negócio, transações, orquestração de repos   | Repository, outros Services        |
| **Repository**   | Acesso a dados (Eloquent / Query Builder)             | Model                              |
| **Model**        | Relacionamentos, casts, scopes                        | —                                  |
| **Resource**     | Formatar saída para JSON                              | Model (via `$this`)                |

---

## 🔄 Fluxo de uma Requisição

```
┌──────────────────┐
│  HTTP Request    │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│  FormRequest     │  ← Valida entrada
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│   Controller     │  ← Orquestra (nada de lógica!)
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│    Service       │  ← Regra de negócio + transações
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│   Repository     │  ← Query / Eloquent
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│      Model       │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│    Resource      │  ← Formata saída
└────────┬─────────┘
         │
         ▼
┌──────────────────┐
│  JSON Response   │
└──────────────────┘
```

---

## 📏 Regras por Camada

### 1. FormRequest

✅ **DEVE:**
- Conter todas as regras de validação
- Conter mensagens customizadas
- Implementar `authorize()`
- Ser nomeado `{Action}{Model}Request` (`StoreUserRequest`, `UpdateUserRequest`)

❌ **NÃO DEVE:**
- Conter regra de negócio
- Fazer queries (exceto em `Rule::unique`)
- Chamar Services

---

### 2. Controller

✅ **DEVE:**
- Ter apenas **5 métodos padrão**: `index`, `store`, `show`, `update`, `destroy`
- Injetar o Service via construtor
- Receber `FormRequest` como parâmetro (nunca `Request`)
- Retornar `Resource` ou `JsonResponse`
- Ser **fino** — no máximo 5 linhas por método

❌ **NÃO DEVE:**
- Conter `if/else` de regra de negócio
- Fazer query direta no Model
- Fazer validação inline (`$request->validate()`)
- Instanciar Services manualmente (`new UserService()`)
- Retornar array/JSON bruto

---

### 3. Service

✅ **DEVE:**
- Conter toda a regra de negócio
- Usar `DB::transaction()` para operações de escrita
- Receber e retornar **Models** ou **DTOs**, nunca `Request`
- Ser testável de forma isolada
- Ter sufixo `Service`

❌ **NÃO DEVE:**
- Acessar `Request`, `Auth::user()` diretamente (receber via parâmetro)
- Retornar `JsonResponse` ou `Resource`
- Conhecer detalhes de HTTP
- Conter queries SQL diretas (isso é do Repository)

---

### 4. Repository

✅ **DEVE:**
- Conter todas as queries (Eloquent/Query Builder)
- Implementar uma interface em `Contracts/`
- Ser vinculado no `AppServiceProvider`
- Retornar Models / Collections / Paginators

❌ **NÃO DEVE:**
- Conter regra de negócio
- Conhecer HTTP, Request ou Response
- Fazer validação

---

### 5. Model

✅ **DEVE:**
- Definir `$fillable` ou `$guarded`
- Conter relacionamentos (`hasMany`, `belongsTo`, etc.)
- Conter casts, accessors e mutators
- Conter scopes de query reutilizáveis

❌ **NÃO DEVE:**
- Conter regra de negócio complexa
- Fazer chamadas HTTP ou a Services

---

### 6. Resource

✅ **DEVE:**
- Formatar todos os campos de saída
- Ocultar campos sensíveis (senha, tokens)
- Ser usado **em todas** as respostas da API

❌ **NÃO DEVE:**
- Conter lógica condicional complexa
- Fazer queries

---

## 💻 Exemplos de Código

### FormRequest

```php
<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
```

### Controller

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function __construct(
        protected UserService $service
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return UserResource::collection(
            $this->service->paginate(request()->integer('per_page', 15))
        );
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->service->create($request->validated());

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): UserResource
    {
        return new UserResource($this->service->find($id));
    }

    public function update(UpdateUserRequest $request, int $id): UserResource
    {
        return new UserResource(
            $this->service->update($id, $request->validated())
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json(null, 204);
    }
}
```

### Service

```php
<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $repository
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function find(int $id): User
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $data['password'] = Hash::make($data['password']);
            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): User
    {
        return DB::transaction(function () use ($id, $data) {
            $user = $this->repository->findOrFail($id);

            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            return $this->repository->update($user, $data);
        });
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $this->repository->delete(
                $this->repository->findOrFail($id)
            );
        });
    }
}
```

### Repository + Interface

```php
<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function paginate(int $perPage): LengthAwarePaginator;
    public function findOrFail(int $id): User;
    public function create(array $data): User;
    public function update(User $user, array $data): User;
    public function delete(User $user): void;
}
```

```php
<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(protected User $model) {}

    public function paginate(int $perPage): LengthAwarePaginator
    {
        return $this->model->latest()->paginate($perPage);
    }

    public function findOrFail(int $id): User
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
```

### Resource

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
```

### Bind no ServiceProvider

```php
// app/Providers/AppServiceProvider.php

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\UserRepository;

public function register(): void
{
    $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
}
```

### Rotas

```php
// routes/api.php

use App\Http\Controllers\UserController;

Route::apiResource('users', UserController::class);
```

---

## 🚫 Regras Proibidas (Anti-patterns)

| ❌ Anti-pattern                                        | ✅ Correto                                              |
| ----------------------------------------------------- | ------------------------------------------------------ |
| `$request->validate([...])` no Controller             | Usar `FormRequest`                                     |
| `User::where(...)->get()` no Controller               | Mover para `Repository`                                |
| `new UserService()` no Controller                     | Injeção via construtor                                 |
| `DB::beginTransaction()` no Controller                | Usar `DB::transaction()` no `Service`                  |
| Retornar `return $user;` direto                       | Retornar `new UserResource($user)`                     |
| Lógica `if/else` de negócio no Controller             | Mover para `Service`                                   |
| `Auth::user()` dentro do Service                      | Receber usuário via parâmetro                          |
| Service retornando `JsonResponse`                     | Service retorna `Model`; Controller converte           |
| Controller com mais de 100 linhas                     | Quebrar em Service                                     |
| Método `store` com mais de 10 linhas                  | Extrair para Service                                   |

---

## ✅ Checklist de Pull Request

Antes de abrir um PR, verifique:

### Controller
- [ ] Fino (máx. 5 linhas por método)
- [ ] Service injetado via construtor
- [ ] Usa `FormRequest` (não `Request` direto)
- [ ] Retorna `Resource` ou `JsonResponse`
- [ ] Sem regra de negócio

### Service
- [ ] Contém toda a regra de negócio
- [ ] Usa `DB::transaction()` em operações de escrita
- [ ] Não acessa `Request` diretamente
- [ ] Retorna Models/DTOs, nunca `Response`

### Repository
- [ ] Implementa interface em `Contracts/`
- [ ] Bindado no `AppServiceProvider`
- [ ] Toda query está aqui (nada no Service/Controller)

### FormRequest
- [ ] Validação completa
- [ ] Mensagens customizadas (quando necessário)
- [ ] `authorize()` implementado

### Resource
- [ ] Campos sensíveis ocultos
- [ ] Formato consistente com outros Resources

---

## 📛 Convenções de Nomenclatura

| Tipo             | Padrão                    | Exemplo                    |
| ---------------- | ------------------------- | -------------------------- |
| Controller       | `{Model}Controller`       | `UserController`           |
| Service          | `{Model}Service`          | `UserService`              |
| Repository       | `{Model}Repository`       | `UserRepository`           |
| Interface        | `{Model}RepositoryInterface` | `UserRepositoryInterface` |
| FormRequest      | `{Action}{Model}Request`  | `StoreUserRequest`         |
| Resource         | `{Model}Resource`         | `UserResource`             |
| Métodos Service  | Verbo + contexto          | `create`, `update`, `find` |

### Verbos padronizados para Services

| Ação             | Método                |
| ---------------- | --------------------- |
| Listar paginado  | `paginate()`          |
| Buscar 1         | `find()`              |
| Criar            | `create()`            |
| Atualizar        | `update()`            |
| Remover          | `delete()`            |
| Restaurar        | `restore()`           |

---

## 🧪 Testes (recomendado)

Cada camada tem seu tipo de teste:

| Camada       | Tipo de Teste         | Ferramenta         |
| ------------ | --------------------- | ------------------ |
| Controller   | Feature (HTTP)        | Pest / PHPUnit     |
| Service      | Unitário              | Pest / PHPUnit     |
| Repository   | Integração (DB)       | Pest / PHPUnit     |
| FormRequest  | Feature               | Pest / PHPUnit     |

### Exemplo — Teste de Controller

```php
it('creates a user', function () {
    $response = $this->postJson('/api/users', [
        'name'     => 'John Doe',
        'email'    => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
});
```

### Exemplo — Teste de Service

```php
it('hashes password on create', function () {
    $service = app(UserService::class);
    $user = $service->create([
        'name'     => 'Jane',
        'email'    => 'jane@example.com',
        'password' => 'secret123',
    ]);

    expect(Hash::check('secret123', $user->password))->toBeTrue();
});
```

---

## 📚 Referências

- [Laravel Docs — Controllers](https://laravel.com/docs/controllers)
- [Laravel Docs — Form Requests](https://laravel.com/docs/validation#form-request-validation)
- [Laravel Docs — API Resources](https://laravel.com/docs/eloquent-resources)
- Clean Architecture — Robert C. Martin
- Domain-Driven Design — Eric Evans

---

**Última atualização:** {{ data }}
**Mantido por:** Time de Engenharia