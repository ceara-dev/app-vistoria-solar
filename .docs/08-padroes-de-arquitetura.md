# 08. Padrões de arquitetura

A pasta `.projects/` traz três documentos normativos, mais dois trechos soltos:

| Arquivo | Conteúdo |
|---|---|
| `deepseek_markdown_20261015_12216c.md` | Backend: FormRequest → Controller → Service → Repository → Model, com saída por Resource |
| `deepseek_markdown_20261015_d77cf7.md` | Frontend como SPA: Vue Router, Pinia, Axios, TypeScript, pasta `src/` |
| `deepseek_markdown_20261015_cb21c2.md` | O mesmo frontend dividido em **Central** (`/central/*`) e **Tenant** (`/app/*`) |
| `deepseek_markdown_20261015_4c6b0f.md` | Trecho de README apontando para `docs/ARCHITECTURE.md` e `docs/FRONTEND.md` |
| `deepseek_ts_20261015_7bbd66.ts` | Alias `@` → `./src` no Vite |

Os documentos são modelos genéricos (usuários, produtos, pedidos) e partem de uma SPA. Este projeto usa Inertia. **Decidido em 15/10/2026:** manter o Inertia, adotar TypeScript e aplicar as regras de `.projects` onde elas fazem sentido no Inertia. Este documento registra como cada regra fica.

## 1. O que muda em relação a `.projects`

| Regra em `.projects` | Neste projeto | Motivo |
|---|---|---|
| Vue Router e `router/*.ts` | Rotas em `routes/web.php`, URLs por `route()` do Ziggy | No Inertia quem roteia é o Laravel |
| Axios, `services/http.ts` e token no `localStorage` | `useForm` e `router` do Inertia. `useHttp` (Inertia v3) para requisições avulsas | O Inertia v3 nem traz o Axios. A sessão por cookie `HttpOnly` não deixa token exposto a XSS |
| Pinia (`auth`, `tenantContext`, `notification`) | Props compartilhadas (`auth`, `tenant`, `tenants`, `can`) lidas com `usePage()`, e mensagens por *flash* | O servidor já entrega esse estado em toda página. Sem dependência nova |
| Pasta `src/` e alias `@` → `./src` | `resources/js/` e alias `@` → `resources/js` (já configurado no `vite.config.js`) | Estrutura do Laravel |
| Página busca dados no `onMounted` | O controller entrega os dados como props | Menos requisições, e a página já chega pronta |
| Controller com 5 métodos | Web: 7 métodos (`create` e `edit` renderizam os formulários). API do app: 5 métodos (`apiResource`) | O padrão de front exige `Create.vue` e `Edit.vue`, e alguém precisa renderizá-las |
| Controller devolve `JsonResponse` | Web: `Inertia::render()` com Resources nas props, e `redirect()` após gravar. API: Resource | Contrato do Inertia |
| `show(int $id)` + `service->find($id)` | `show(Customer $customer)` com *route model binding* | Com o escopo da empresa, o *binding* já devolve 404 para outra empresa e a policy recebe o model |
| Constantes com caminhos (`'/app/users'`) | Constantes só com **nomes** de rota (`'tenant.customers.index'`) | O Ziggy gera a URL. Caminho escrito à mão quebra quando a rota muda |
| Vitest | Vitest para `Utils` e `Composables`, em `tests/js/**/*.spec.ts` (`npm run test:unit`). Aprovado e instalado na fase 2. O Playwright já está no `package.json` para E2E | |
| Pastas em minúsculas (`components/`, `pages/`) | Mantém a capitalização que já existe: `Pages`, `Layouts`, `Components`, `Constants`. As novas seguem o mesmo padrão: `Composables`, `Types`, `Utils` | O Inertia resolve `./Pages/...` e o código atual já usa essa forma |

## 2. Backend

### Camadas

```
FormRequest → Controller → Service → Repository (+ Contract) → Model
                  │
                  └→ Resource (props do Inertia ou JSON da API)
```

Valem as regras do documento de backend de `.projects`, com estes acréscimos para multi-tenant:

| Camada | Regra de multi-tenant |
|---|---|
| FormRequest | `exists` e `unique` de tabelas de empresa usam `TenantRule` ([doc 04, seção 12](04-implementacao-backend.md#12-validação-e-consultas-fora-do-eloquent)). O exemplo `'unique:users,email'` de `.projects` continua certo porque `users` é global. `authorize()` chama a policy |
| Controller | Nunca lê `tenant_id` do request. Se o Service precisa do usuário, o controller passa `$request->user()`. Quando o próprio assunto é a empresa (membros, convites), o controller passa o `Tenant` do `TenantContext` |
| Service | Não conhece a empresa. Não recebe `Request` nem chama `Auth::user()`, como já manda o padrão. Usa `DB::transaction()` |
| Repository | As consultas Eloquent já saem filtradas pelo escopo global. `DB::table()`, SQL cru e `withoutGlobalScope` são proibidos fora de `Repositories/Central/`. Exceção única, comentada no código: `TenantInvitationRepository::findPendingByTokenHash()`, porque quem abre o link do convite ainda não está em nenhuma empresa |
| Model | `use BelongsToTenant` e `tenant_id` fora do `#[Fillable]` |
| Resource | Não expõe `tenant_id`, que o front não precisa |
| Policy | Confere a permissão do papel (`TenantPermission`) e, nas ações sobre um registro, se ele é da empresa atual. Molde: `TenantInvitationPolicy` ([doc 04, seção 7](04-implementacao-backend.md#como-autorizar)) |

### Pastas

As camadas novas (`app/Services`, `app/Repositories`, `app/Repositories/Contracts`, `app/Http/Resources`) são criadas junto com o primeiro módulo que as usar. Elas fazem parte dos padrões adotados.

- **Controllers e Requests** são separados por contexto: `Http/Controllers/Tenant/CustomerController.php` e `Http/Controllers/Central/TenantController.php`; `Http/Requests/Tenant/Customer/StoreCustomerRequest.php`.
- **Services, Repositories e Resources** são separados por domínio, como no padrão. A exceção é o que atravessa empresas, que fica em `Central/` (`Repositories/Central/TenantRepository.php`). Assim fica fácil revisar todo `withoutGlobalScope`.

### Exemplo: criar um cliente

```php
// Http/Controllers/Tenant/CustomerController.php
public function store(StoreCustomerRequest $request): RedirectResponse
{
    $this->service->create($request->validated());

    return to_route('tenant.customers.index');
}

// Services/CustomerService.php
public function create(array $data): Customer
{
    return DB::transaction(fn (): Customer => $this->repository->create($data));
}

// Repositories/CustomerRepository.php
public function create(array $data): Customer
{
    return Customer::create($data); // o trait preenche tenant_id
}
```

Nenhuma das três camadas fala de empresa, mas o cliente é gravado na empresa certa e só aparece para ela.

## 3. Frontend

### Estrutura

```
resources/js/
├── app.ts
├── Types/
│   ├── global.d.ts            # route() do Ziggy e props compartilhadas do Inertia
│   ├── tenant/customer.ts
│   └── central/tenant.ts
├── Constants/
│   ├── routes/{tenant,central,auth,index}.ts   # só nomes de rota
│   ├── messages.ts
│   ├── status.ts
│   └── index.ts
├── Utils/{format,validators,string,index}.ts
├── Composables/{shared,tenant,central}/
├── Components/{common,tenant,central}/
├── Layouts/{TenantLayout,CentralLayout,GuestLayout}.vue
└── Pages/
    ├── Welcome.vue
    ├── Auth/...
    ├── Tenant/{Dashboard,Customers,...}/{Index,Create,Edit,Show}.vue
    └── Central/{Dashboard,Tenants,Plans,Invoices}/...
```

- **Central e Tenant não se importam entre si**, como mandam os padrões. O que serve aos dois vai para `Components/common` ou `Composables/shared`.
- **Componentes comuns** recebem o prefixo `App`. Os atuais viram `AppFormField`, `AppTextInput`, `AppPasswordInput` e `AppPasswordStrength` na migração para TypeScript.
- `GuestLayout` continua sendo o layout das telas de autenticação. Ele faz o papel do `AuthLayout` dos padrões.

### Props compartilhadas tipadas

O Inertia v3 tipa as props compartilhadas pela interface `InertiaConfig`:

```ts
// resources/js/Types/global.d.ts
import '@inertiajs/core'

declare module '@inertiajs/core' {
  export interface InertiaConfig {
    sharedPageProps: {
      auth: { user: { id: number; name: string; email: string } | null }
      tenant: { id: number; name: string } | null
      tenants: Array<{ id: number; name: string }>
      can: string[]
    }
  }
}
```

### Pages magras, lógica em composables

```ts
// resources/js/Composables/tenant/useCustomerForm.ts
import { useForm } from '@inertiajs/vue3'
import { ROUTE_NAMES_TENANT } from '@/Constants'
import type { Customer, CustomerPayload } from '@/Types/tenant/customer'

export function useCustomerForm(customer?: Customer) {
  const form = useForm<CustomerPayload>({
    name: customer?.name ?? '',
    document: customer?.document ?? '',
  })

  function submit(): void {
    if (customer) {
      form.put(route(ROUTE_NAMES_TENANT.CUSTOMERS.UPDATE, customer.id))
    } else {
      form.post(route(ROUTE_NAMES_TENANT.CUSTOMERS.STORE))
    }
  }

  return { form, submit }
}
```

```vue
<!-- resources/js/Pages/Tenant/Customers/Create.vue -->
<script setup lang="ts">
import TenantLayout from '@/Layouts/TenantLayout.vue'
import CustomerForm from '@/Components/tenant/customers/CustomerForm.vue'
import { useCustomerForm } from '@/Composables/tenant/useCustomerForm'

const { form, submit } = useCustomerForm()
</script>

<template>
  <TenantLayout title="Novo cliente">
    <CustomerForm :form="form" @submit="submit" />
  </TenantLayout>
</template>
```

As páginas `Index.vue` e `Show.vue` recebem os dados como props tipadas (`defineProps<{ customers: Paginated<Customer> }>()`). Não há `onMounted` buscando dados.

### Migração para TypeScript

Entra no começo da fase 2, antes das telas novas ([doc 07](07-plano-de-implementacao.md)):

1. Instalar `typescript` e `vue-tsc` (aprovados em 15/10/2026) e criar `tsconfig.json` com `paths` `@/*` → `resources/js/*`.
2. `app.js` → `app.ts`, ajustar o `input` no `vite.config.js` e no `@vite` de `app.blade.php`.
3. `Types/global.d.ts` com o `route()` do Ziggy e as props compartilhadas.
4. Converter `Constants`, `Components`, `Layouts` e `Pages` existentes para `<script setup lang="ts">`, renomeando os componentes comuns com o prefixo `App`.
5. Script `"typecheck": "vue-tsc --noEmit"` no `package.json`.

## 4. Contrato de validação (`VALIDATION.md`)

O `.docs/VALIDATION.md` define que toda regra de validação existe nos dois lados, com mensagens idênticas. No Inertia e no multi-tenant, ele se aplica assim:

| Regra do contrato | Neste projeto |
|---|---|
| Backend devolve 422 com `message` e `errors` | Vale para a API do app. Numa requisição Inertia, o Laravel redireciona de volta com os erros na sessão, e o `useForm` os coloca em `form.errors` sozinho. O composable só precisa rolar até o primeiro erro |
| Mensagens em `FormRequest::messages()` | As mensagens padrão ficam em `lang/pt_BR/validation.php`, uma vez só para todos os FormRequests. `messages()` fica para exceções. Hoje `APP_LOCALE=en` e não existe a pasta `lang/`: as mensagens saem em inglês. Corrigir com `APP_LOCALE=pt_BR` e `php artisan lang:publish`, traduzindo para os textos da tabela do contrato |
| Mensagens do front em `MESSAGES.VALIDATION.*` | `Constants/messages.ts`, com os mesmos textos e as chaves do contrato (`REQUIRED`, `MAX_LENGTH`...) |
| Regras customizadas em `app/Rules/` | `Cpf`, `Cnpj`, `PhoneBr`, `BusinessHours`, `MinAge`, criadas com `php artisan make:rule`. Os validadores equivalentes ficam em `Utils/validators.ts` |
| `unique` e `exists` só no backend | São justamente as regras que precisam da empresa: em tabelas de empresa, sempre por `TenantRule` ([doc 04, seção 12](04-implementacao-backend.md#12-validação-e-consultas-fora-do-eloquent)). Um `unique` sem empresa diria a um usuário que um CPF "já está em uso" por um cliente de **outra** empresa, o que vaza informação |
| `AppInput` com contador, `AppDatePicker`, `AppTimePicker` | Entram em `Components/common` na migração para TypeScript (seção 3) |
| Links para `docs/ARCHITECTURE.md` e `docs/FRONTEND.md` | Esses arquivos não existem, então os dois links do `VALIDATION.md` estão quebrados (seção 5) |

## 5. Onde os padrões de `.projects` não foram seguidos

- **Token no `localStorage`** (exemplo de `http.ts`): não é usado na web, por segurança. No app Flutter, o token fica no armazenamento seguro do aparelho ([doc 05](05-api-e-app-mobile.md#4-app-flutter-app)).
- **`Tenant.domain` obrigatório no `CreateTenantPayload`**: a empresa é identificada pela sessão, e `domain` fica opcional e reservado ([doc 02, seção 3](02-estrategia-multi-tenant.md#3-como-identificar-a-empresa)).
- **Papéis `admin | manager | user`** no tipo `User` do tenant: os papéis deste projeto são `proprietario`, `administrador`, `gestor` e `vistoriador` ([doc 02, seção 4](02-estrategia-multi-tenant.md#papéis)). O tipo TypeScript usa esses nomes.
- **Os documentos de `.projects` citam `docs/ARCHITECTURE.md` e `docs/FRONTEND.md`.** Esses arquivos ainda não existem. Se a ideia é versioná-los, o lugar natural é `.docs/`, já adaptados conforme este documento.
