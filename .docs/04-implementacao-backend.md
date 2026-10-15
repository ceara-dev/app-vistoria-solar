# 04. Implementação no backend

Os trechos de código são o desenho proposto. Eles seguem as convenções do projeto (PHP 8.4, tipos explícitos, PHPDoc, atributos do Laravel 13) e devem ser criados com `php artisan make:*` na hora de implementar.

## 1. Visão geral

```
Requisição web
  └─ grupo web: sessão, CSRF, HandleInertiaRequests (props da empresa são closures)
  └─ auth                    → usuário
  └─ tenant (SetCurrentTenant) → TenantContext::set($empresa)
         ├─ setPermissionsTeamId($empresa->id)      (spatie)
         └─ Context::add('tenant_id', $empresa->id) (logs e filas)
  └─ SubstituteBindings      → {customer} já é buscado com o escopo da empresa
  └─ controller              → Customer::query() filtra por tenant_id sozinho
```

| Peça | Arquivo proposto |
|---|---|
| Model `Tenant` e enum `TenantStatus` | `app/Models/Tenant.php`, `app/Tenancy/TenantStatus.php` |
| Contexto da empresa atual | `app/Tenancy/TenantContext.php` |
| Escopo global | `app/Tenancy/TenantScope.php` |
| Trait dos models da empresa | `app/Tenancy/BelongsToTenant.php` |
| Regras de validação | `app/Tenancy/TenantRule.php` |
| Resolver da auditoria | `app/Tenancy/TenantIdResolver.php` |
| Exceção | `app/Tenancy/TenantNotResolvedException.php` |
| Middleware web | `app/Http/Middleware/SetCurrentTenant.php` |

`app/Tenancy` é uma pasta nova, aprovada em 15/10/2026. O enum fica nela para não abrir outra pasta de base.

O código de domínio (controllers, services, repositories, resources) segue as camadas descritas no [doc 08](08-padroes-de-arquitetura.md). As peças de tenancy deste documento ficam **abaixo** dessas camadas: services e repositories não recebem nem consultam a empresa, porque o escopo global e o trait já fazem isso.

## 2. `TenantContext`

É a única fonte da verdade sobre "qual empresa está ativa agora". Ele é registrado como `scoped`, então o container o descarta ao fim de cada requisição e antes de cada job no worker (`QueueServiceProvider` chama `forgetScopedInstances()`).

```php
// AppServiceProvider::register()
$this->app->scoped(TenantContext::class);
```

```php
namespace App\Tenancy;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Context;

/**
 * Empresa da requisição ou do job em execução.
 */
class TenantContext
{
    private ?Tenant $tenant = null;

    public function set(?Tenant $tenant): void
    {
        $this->tenant = $tenant;

        setPermissionsTeamId($tenant?->id);

        if ($tenant === null) {
            Context::forget('tenant_id');
        } else {
            Context::add('tenant_id', $tenant->id);
        }
    }

    public function get(): ?Tenant
    {
        return $this->tenant;
    }

    public function id(): ?int
    {
        return $this->tenant?->id;
    }

    /**
     * @throws TenantNotResolvedException
     */
    public function require(): Tenant
    {
        return $this->tenant ?? throw new TenantNotResolvedException;
    }

    /**
     * Indica se o registro é da empresa atual. Sem empresa no contexto, nada é.
     */
    public function owns(Model $model): bool
    {
        return $this->tenant !== null && (int) $model->getAttribute('tenant_id') === $this->tenant->id;
    }

    /**
     * Executa o callback dentro da empresa informada e restaura a anterior.
     *
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public function run(Tenant $tenant, callable $callback): mixed
    {
        $previous = $this->tenant;
        $this->set($tenant);

        try {
            return $callback();
        } finally {
            $this->set($previous);
        }
    }
}
```

`tenant_id` vai no `Context` **visível**, não no oculto, de propósito. Assim todo log sai com a empresa, o que ajuda no suporte. Não é um dado sensível.

## 3. Escopo global (`TenantScope`)

```php
namespace App\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * @throws TenantNotResolvedException
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where(
            $model->qualifyColumn('tenant_id'),
            app(TenantContext::class)->require()->id,
        );
    }
}
```

**Falha fechada e com barulho.** Sem empresa no contexto, a consulta lança `TenantNotResolvedException` em vez de trazer as linhas de todo mundo. Um esquecimento aparece logo no primeiro teste, não em produção.

Dois detalhes:

- **Não trocar por `where('tenant_id', null)`.** O Laravel transforma isso em `IS NULL`, que em tabelas com `tenant_id` nulo (`audits`, `roles`) traria as linhas da plataforma.
- **A Central remove o escopo de forma explícita**, com `Customer::withoutGlobalScope(TenantScope::class)`. Cada uso disso deve chamar atenção no code review.

## 4. Trait `BelongsToTenant`

```php
namespace App\Tenancy;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * Para models cujas linhas pertencem a uma empresa.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model): void {
            $currentTenantId = app(TenantContext::class)->id();

            if ($model->tenant_id === null) {
                $model->tenant_id = $currentTenantId ?? throw new TenantNotResolvedException;

                return;
            }

            if ($currentTenantId !== null && (int) $model->tenant_id !== $currentTenantId) {
                throw new LogicException('Registro de outra empresa criado dentro do contexto atual.');
            }
        });

        static::updating(function (Model $model): void {
            if ($model->isDirty('tenant_id')) {
                throw new LogicException('A empresa de um registro não pode ser alterada.');
            }
        });
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
```

| Situação | Resultado |
|---|---|
| Contexto definido, `tenant_id` vazio | Preenche com a empresa atual (o caso normal) |
| Contexto definido, `tenant_id` igual | Aceita |
| Contexto definido, `tenant_id` diferente | Exceção |
| Sem contexto, `tenant_id` definido explicitamente | Aceita (seeders, factories, Central) |
| Sem contexto, `tenant_id` vazio | Exceção |
| `tenant_id` alterado num update | Exceção |

Como `tenant_id` nunca está no `#[Fillable]`, a única forma de definir a empresa à mão é atribuir o atributo direto ou usar `forceFill`, e isso sempre é intencional.

## 5. Middleware `SetCurrentTenant`

Implementado em `app/Http/Middleware/SetCurrentTenant.php` (fase 1). Em resumo:

```php
public function handle(Request $request, Closure $next): Response
{
    $user = $request->user() ?? throw new AuthenticationException;
    $tenant = $this->currentTenantResolver->resolve($user);

    if ($tenant === null) {
        return to_route('tenants.select');
    }

    if (! $tenant->allowsAccess()) {
        return to_route('tenants.blocked');
    }

    $this->tenantContext->set($tenant);

    return $next($request);
}
```

`CurrentTenantResolver::resolve()` (`app/Tenancy`) mantém `current_tenant_id` enquanto o vínculo estiver ativo. Se o vínculo caiu ou não há empresa atual, adota a única empresa ativa da pessoa. Com mais de uma empresa ativa, limpa `current_tenant_id` e devolve `null`, para a pessoa escolher. A regra fica numa classe própria, e não no model, porque grava no banco e vai ser reaproveitada pelo middleware da API.

Na fase 3, depois de `set()`, entra `$user->unsetRelation('roles')->unsetRelation('permissions')` (seção 7).

### Registro e ordem

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->web(append: [
        HandleInertiaRequests::class,
    ]);

    $middleware->alias([
        'tenant' => SetCurrentTenant::class,
    ]);

    // A empresa precisa estar definida antes do route model binding.
    $middleware->prependToPriorityList(SubstituteBindings::class, SetCurrentTenant::class);
})
```

**A prioridade é obrigatória.** `SubstituteBindings` está no grupo `web` e, sem ela, rodaria antes de `tenant`. Aí `{customer}` seria buscado sem empresa no contexto, e o escopo lançaria exceção.

### Rotas

Implementadas na fase 2 (`routes/web.php`), com URLs em inglês como nos padrões de `.projects`:

| Rota | Nome | Middleware | Para quê |
|---|---|---|---|
| `GET /tenants` | `tenants.select` | `auth`, `verified` | Escolher a empresa ou criar uma nova |
| `POST /tenants` | `tenants.store` | `auth`, `verified` | Criar empresa (a pessoa vira proprietária) |
| `PUT /current-tenant` | `current-tenant.update` | `auth`, `verified` | Trocar de empresa |
| `GET /tenant-blocked` | `tenants.blocked` | `auth`, `verified` | Aviso de empresa sem acesso |
| `GET /app/dashboard` | `tenant.dashboard` | `+ tenant` | Painel |
| `GET /app/members` | `tenant.members.index` | `+ tenant`, `can:members.view` | Membros e convites pendentes |
| `PUT /app/members/{member}` | `tenant.members.update` | `+ tenant` (FormRequest checa `members.manage`) | Mudar papel |
| `DELETE /app/members/{member}` | `tenant.members.destroy` | `+ tenant`, `can:members.manage` | Remover acesso |
| `POST /app/invitations` | `tenant.invitations.store` | `+ tenant` (FormRequest checa `members.manage`) | Convidar ou reenviar convite |
| `DELETE /app/invitations/{invitation}` | `tenant.invitations.destroy` | `+ tenant`, `can:members.manage` | Cancelar convite |
| `GET/POST /invitations/{token}` | `invitations.show` / `invitations.accept` | `throttle:20,1` | Link do e-mail, com ou sem login |

- As rotas de escolha e de bloqueio **não** levam `tenant`, para não criar redirecionamento em círculo.
- A troca de empresa valida o vínculo ativo no `SwitchTenantRequest`. Empresa alheia volta como erro de validação em `tenant_id`, sem revelar se ela existe.
- `{member}` é um `User`, que não tem escopo de empresa. O `AppServiceProvider` faz o *binding* pelos membros da empresa atual, e quem não é membro dá 404.

## 6. Models `Tenant` e `User`

```php
#[Fillable(['name', 'legal_name', 'document', 'settings'])]
class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => TenantStatus::class,
            'trial_ends_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('is_active')->withTimestamps();
    }

    public function allowsAccess(): bool
    {
        return match ($this->status) {
            TenantStatus::Active => true,
            TenantStatus::Trial => $this->trial_ends_at === null || $this->trial_ends_at->isFuture(),
            TenantStatus::Suspended, TenantStatus::Canceled => false,
        };
    }
}
```

Em `User`:

- `tenants()`: `belongsToMany` com o pivot `is_active`.
- `currentTenant()`: `belongsTo(Tenant::class, 'current_tenant_id')`.
- `isActiveMemberOf(Tenant $tenant): bool`.
- `activeTenants()`: `tenants()` filtrado por `is_active`.
- `use HasRoles` (spatie).
- `is_central_admin` com cast `boolean`, fora do `#[Fillable]`.

### Cadastro

Pelas camadas do [doc 08](08-padroes-de-arquitetura.md), `RegisteredUserController@store` recebe um `RegisterRequest` e chama `RegistrationService::register()`, que faz tudo numa transação usando `TenantRepository` e `UserRepository`:

1. Cria o `Tenant` com `status = trial` e `trial_ends_at`.
2. Cria o `User`.
3. Cria o vínculo em `tenant_user` e define `current_tenant_id`.
4. Chama `setPermissionsTeamId($tenant->id)` e depois `$user->assignRole('proprietario')`.

O formulário `Auth/Register.vue` ganha o campo "Nome da empresa".

## 7. Papéis e permissões (spatie com teams)

Implementado nas fases 2 e 3.

- **Config:** `config/permission.php` com `'teams' => true` e `'team_foreign_key' => 'tenant_id'`, ligados **antes** da migration do pacote ([doc 03](03-modelo-de-dados.md#3-tabelas-de-pacotes)).
- **Papéis e permissões são enums:** `TenantRole` (`proprietario`, `administrador`, `gestor`, `vistoriador`, com `label()` e `permissions()`) e `TenantPermission` (`members.view`, `members.manage`). O front espelha as permissões em `Constants/permissions.ts`.
- **`RolesAndPermissionsSeeder`:** cria papéis globais (`tenant_id` nulo) e sincroniza as permissões. Pode rodar quantas vezes for preciso e precisa rodar a cada deploy que mudar os enums. Ele limpa o cache do spatie no meio do processo, porque o `DatabaseSeeder` roda com `WithoutModelEvents`.
- **Atribuição de papel** só pelo `MemberRepository::assignRole()`, que roda dentro da empresa (`TenantContext::run`) e grava o histórico (seção 8).
- **Cada módulo novo** acrescenta as próprias permissões a `TenantPermission` (`customers.view`, `customers.manage`...) e a `TenantRole::permissions()`.

### Como autorizar

| Caso | Como | Exemplo |
|---|---|---|
| Módulo com model próprio | Policy que confere a permissão do papel e, nas ações sobre um registro, `TenantContext::owns()` | `CustomerPolicy` (`viewAny`, `view`, `create`, `update`, `delete`), ligada com `middlewareFor()` no `Route::resource` e com `authorize()` nos FormRequests. `TenantInvitationPolicy` segue o mesmo molde |
| Módulo sem model próprio | Permissão direto | Membros são `User`, um model global. Rotas com `->can(TenantPermission::MembersView->value)` |
| Regra que depende do dado | Service, com `ValidationException` | Ninguém altera o próprio acesso; só proprietário mexe em proprietário (`MemberService`) |

A comparação de empresa na policy é um reforço. O escopo global já impede carregar um registro de outra empresa, então pelo HTTP a resposta continua sendo 404. A policy protege os caminhos que não passam pelo *binding*, como jobs e comandos.

**Armadilha:** o spatie guarda em memória as relações `roles` e `permissions` do usuário. Depois de trocar a empresa no meio da execução (num `TenantContext::run` ou num teste), chame `$user->unsetRelation('roles')->unsetRelation('permissions')`. O middleware `tenant` já faz isso a cada requisição, e o helper `actAsMemberIn()` dos testes também.

## 8. Auditoria

Implementado na fase 3.

| Peça | Arquivo |
|---|---|
| Coluna `audits.tenant_id` (nula, FK, índice `(tenant_id, created_at)`) | `database/migrations/*_add_tenant_id_to_audits_table.php` |
| Empresa de cada auditoria | `App\Tenancy\TenantIdResolver`, em `audit.resolvers.tenant_id` |
| Histórico só da empresa atual | `App\Models\Audit` com `#[ScopedBy(TenantScope::class)]`, em `audit.implementation` |
| Vínculos e papéis no histórico | `App\Tenancy\TenantAuditTrail` e o enum `MembershipEvent` |

**De onde vem a empresa** (`TenantIdResolver`), nesta ordem:

1. o próprio `Tenant`, quando ele é o registro auditado;
2. o `tenant_id` do registro auditado, que vale mesmo sem empresa no contexto (aceite de convite, Central, console);
3. a empresa do contexto;
4. nulo, para ações da Central.

**O que é auditado hoje:**

| Model ou evento | Eventos | Observação |
|---|---|---|
| `Tenant` | `created`, `updated`, `deleted` | |
| `TenantInvitation` | `created`, `updated`, `deleted` | `token_hash` fica fora (`$auditExclude`) |
| `member_activated`, `member_deactivated` | Evento customizado no `Tenant` | `old_values`/`new_values`: `{user_id, is_active}` |
| `member_role_changed` | Evento customizado no `Tenant` | `{user_id, role}` antes e depois |

**Por que eventos customizados para vínculos e papéis:** o `auditSync()` do pacote compara só as chaves dos registros, então mudar `is_active` de um membro gerava uma auditoria vazia, e cada chamada carregava todos os membros. O `TenantAuditTrail` grava valores antigos e novos explícitos. Chamadas que não mudam nada não geram auditoria.

**Configurações de `config/audit.php` alteradas:**

- `user.guards` só com `web`. O `sanctum` entra na fase 5.
- `console` ligado (`AUDIT_CONSOLE`, padrão `true`). Workers de fila e comandos também rodam no console e precisam deixar histórico. O `DatabaseSeeder` não audita porque usa `WithoutModelEvents`.

**Ainda não existe tela de histórico.** As auditorias ficam no banco, separadas por empresa. A tela entra quando um módulo precisar dela.

## 9. Filas, console e agendador

### Jobs

O Laravel já serializa o `Context` no payload de cada job e o restaura no evento `JobProcessing`, disparando `Hydrated` mesmo quando o job não tem contexto. Basta reconstruir o `TenantContext` a partir dele:

```php
// AppServiceProvider::boot()
Context::hydrated(function (Repository $context): void {
    $tenantId = $context->get('tenant_id');

    app(TenantContext::class)->set($tenantId === null ? null : Tenant::find($tenantId));
});
```

- Todo job, listener, notificação ou e-mail enfileirado dentro de uma requisição de empresa roda na mesma empresa, sem código extra.
- Como `set()` também chama `setPermissionsTeamId()`, o spatie não herda a empresa do job anterior. O `PermissionRegistrar` é *singleton* e sobreviveria no worker.
- `SerializesModels` restaura models com `newQueryWithoutScopes()`, então os models do job carregam mesmo antes do contexto. As relações carregadas depois já usam o escopo.

### Comandos e agendador

Comandos nunca herdam uma empresa. Quando a tarefa é por empresa, o comando percorre as empresas:

```php
Tenant::query()
    ->where('status', TenantStatus::Active)
    ->each(fn (Tenant $tenant) => $tenantContext->run(
        $tenant,
        fn () => SendDailyAgenda::dispatch(),
    ));
```

## 10. Arquivos

- Disco `local` (privado, `storage/app/private`), caminho `tenants/{tenant_id}/{módulo}/{uuid do registro}/{arquivo}`.
- Nunca usar o disco `public` para fotos de vistoria ou documentos de cliente. Com `storage:link`, quem tem o caminho baixa o arquivo sem login.
- Download sempre por controller: `AttachmentController@show(Attachment $attachment)`. O *binding* já respeita o escopo (404 para outra empresa), a policy confere a permissão e a resposta sai por `Storage::disk('local')->download($attachment->path)`.
- `temporaryUrl()` no disco `local` (que tem `serve => true` no config) serve para o app baixar arquivos, desde que expire em poucos minutos. A URL assinada não confere a empresa no momento do acesso.

## 11. Inertia e front

O middleware do Inertia executa `share()` **antes** do controller e do middleware `tenant`. Por isso, tudo o que depende da empresa precisa ser closure, que só é resolvida na hora de montar a resposta:

```php
public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'auth' => [
            'user' => fn () => $request->user()?->only('id', 'name', 'email'),
        ],
        'tenant' => fn () => app(TenantContext::class)->get()?->only('id', 'name'),
        'tenants' => fn () => $request->user()
            ?->tenants()
            ->wherePivot('is_active', true)
            ->orderBy('name')
            ->get()
            ->map->only('id', 'name'),
        'can' => fn () => $request->user()?->getAllPermissions()->pluck('name') ?? [],
    ];
}
```

- Trocar `'user' => $request->user()` pelo array explícito também resolve o problema 3 do [doc 01](01-diagnostico-atual.md#3-problemas-encontrados).
- `Layouts/TenantLayout.vue` (novo, nome dos padrões de `.projects`) leva a barra que hoje está dentro do `Dashboard.vue`, mais o seletor de empresa: um *dropdown* do daisyUI que chama `router.put(route('current-tenant.update'), { tenant_id })`. A Central tem o próprio `Layouts/CentralLayout.vue`.
- A estrutura de pastas do front, o TypeScript e os tipos das props compartilhadas estão no [doc 08](08-padroes-de-arquitetura.md#3-frontend).
- O front esconde botões com base em `can`, mas quem decide é sempre a policy no backend.
- O `@routes` do Ziggy publica no HTML **todas** as rotas nomeadas. Quando a área `/central` existir, excluir `central.*` em `config/ziggy.php` para as páginas das empresas não listarem esses endereços.

## 12. Validação e consultas fora do Eloquent

O escopo global só vale para consultas Eloquent no model que tem o trait. **Estas formas passam por fora dele:**

| Forma | Correção |
|---|---|
| `'exists:customers,id'`, `Rule::exists(...)` | `TenantRule::exists('customers')` |
| `'unique:customers,document'`, `Rule::unique(...)` | `TenantRule::unique('customers', 'document')->ignore($customer)` |
| `DB::table('customers')`, `DB::select(...)` | Evitar em código de empresa. Se for inevitável, `where('tenant_id', ...)` manual e um teste de isolamento |
| `->join('solar_systems', ...)` num relatório | A tabela do *join* não recebe o escopo. Juntar também por `tenant_id` (`->on('solar_systems.tenant_id', '=', 'customers.tenant_id')`) |
| `withoutGlobalScopes()` | Proibido fora da Central |

```php
namespace App\Tenancy;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

/**
 * Regras exists e unique filtradas pela empresa atual.
 */
final class TenantRule
{
    public static function exists(string $table, string $column = 'id'): Exists
    {
        return Rule::exists($table, $column)->where('tenant_id', app(TenantContext::class)->require()->id);
    }

    public static function unique(string $table, string $column): Unique
    {
        return Rule::unique($table, $column)->where('tenant_id', app(TenantContext::class)->require()->id);
    }
}
```

## 13. Cache e limites de requisição

- O cache usa o driver `database`, que **não tem tags**. Toda chave de dado de empresa leva prefixo: `"tenant:{$tenantId}:dashboard:indicators"`. Vale um método `TenantContext::cacheKey(string $key): string` para ninguém montar a chave na mão.
- O cache do spatie é global e pode continuar assim, porque guarda papéis e permissões de todas as empresas e filtra pela empresa em memória.
- O `RateLimiter` da API usa o token como chave (`$request->user()->currentAccessToken()->id`). Para limitar por empresa (por plano, por exemplo), a chave passa a ser `tenant_id`.
