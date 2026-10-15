# 🎨 Padrão de Arquitetura — Frontend (Multi-Tenant)

> Documento normativo do frontend. Complementa o `docs/ARCHITECTURE.md` (backend).
> **Obrigatório** para todos os desenvolvedores.

---

## 📑 Índice

1. [Visão Geral](#-visão-geral)
2. [Conceito: Central vs Tenant](#-conceito-central-vs-tenant)
3. [Estrutura de Pastas](#-estrutura-de-pastas)
4. [Camadas e Responsabilidades](#-camadas-e-responsabilidades)
5. [Regras por Camada](#-regras-por-camada)
6. [Convenções de Nomenclatura](#-convenções-de-nomenclatura)
7. [Exemplos de Código](#-exemplos-de-código)
8. [Regras Proibidas (Anti-patterns)](#-regras-proibidas-anti-patterns)
9. [Checklist de Pull Request](#-checklist-de-pull-request)

---

## 🎯 Visão Geral

O frontend segue arquitetura **modular por domínio e por contexto**, com forte reuso de **Componentes** e separação clara entre:

- **Pages** — divididas em **`Central/`** (sistema/admin) e **`Tenant/`** (clientes/empresas)
- **Components** — blocos reutilizáveis de UI
- **Composables** — toda a lógica de estado e regras
- **Services** — comunicação HTTP
- **Constants** — valores fixos, enums, rotas, mensagens
- **Utils** — funções puras e helpers
- **Types** — tipagens TypeScript

### Princípios Fundamentais

- **Zero redundância** — se repete 2×, extraia
- **Pages magras** — apenas colam componentes
- **Lógica em Composables** — nunca dentro de `.vue` de Page
- **Componentes puros** — recebem `props`, emitem `events`
- **Constants centralizadas** — nunca hardcode strings mágicas
- **Separação de contexto** — `Central` e `Tenant` nunca se misturam

---

## 🏢 Conceito: Central vs Tenant

Este projeto é **multi-tenant SaaS**, dividido em dois contextos:

| Contexto    | Quem acessa                     | Exemplos de telas                         | Prefixo de rota |
| ----------- | ------------------------------- | ----------------------------------------- | --------------- |
| **Central** | Equipe interna / admin do SaaS  | Gestão de tenants, planos, faturas, logs  | `/central/*`    |
| **Tenant**  | Clientes (empresas/usuários)    | Dashboard, usuários, produtos, pedidos    | `/app/*`        |

### Regras de isolamento

✅ **DEVE:**
- Pages de `Central/` **nunca** importam de `Tenant/` (e vice-versa)
- Composables de `Central/` e `Tenant/` são **separados** (mesmo que similares)
- Services compartilhados ficam na raiz de `services/`
- Componentes comuns ficam em `components/common/`

❌ **NÃO DEVE:**
- Compartilhar Pages entre contextos
- Compartilhar Composables entre contextos
- Hardcodar rota sem saber o contexto

> **Regra de ouro:** Se um componente/composable serve aos dois contextos → mover para `common/` ou `composables/shared/`.

---

## 🗂️ Estrutura de Pastas

```
src/
├── assets/
├── components/
│   ├── common/                     # Genéricos (usados em Central + Tenant)
│   │   ├── AppButton.vue
│   │   ├── AppInput.vue
│   │   ├── AppModal.vue
│   │   ├── AppTable.vue
│   │   └── AppPagination.vue
│   ├── central/                    # Específicos do Central
│   │   ├── tenants/
│   │   │   ├── TenantForm.vue
│   │   │   └── TenantTable.vue
│   │   └── plans/
│   │       └── PlanCard.vue
│   └── tenant/                     # Específicos do Tenant
│       ├── users/
│       │   ├── UserForm.vue
│       │   └── UserTable.vue
│       └── dashboard/
│           └── StatsCard.vue
├── composables/
│   ├── shared/                     # Composables usados nos 2 contextos
│   │   ├── useAuth.ts
│   │   └── useNotification.ts
│   ├── central/
│   │   ├── useTenants.ts
│   │   └── usePlans.ts
│   └── tenant/
│       ├── useUsers.ts
│       └── useDashboard.ts
├── constants/
│   ├── routes/
│   │   ├── central.ts              # Rotas do Central
│   │   ├── tenant.ts               # Rotas do Tenant
│   │   ├── auth.ts                 # Login, registro, etc.
│   │   └── index.ts
│   ├── api.ts
│   ├── messages.ts
│   ├── status.ts
│   └── index.ts
├── layouts/
│   ├── CentralLayout.vue           # Layout do admin SaaS
│   ├── TenantLayout.vue            # Layout do cliente
│   └── AuthLayout.vue
├── pages/
│   ├── Central/                    # 🏢 Sistema interno
│   │   ├── Dashboard/
│   │   │   └── Index.vue
│   │   ├── Tenants/
│   │   │   ├── Index.vue
│   │   │   ├── Create.vue
│   │   │   ├── Edit.vue
│   │   │   └── Show.vue
│   │   ├── Plans/
│   │   │   ├── Index.vue
│   │   │   ├── Create.vue
│   │   │   ├── Edit.vue
│   │   │   └── Show.vue
│   │   └── Invoices/
│   │       ├── Index.vue
│   │       └── Show.vue
│   ├── Tenant/                     # 🏬 Cliente
│   │   ├── Dashboard/
│   │   │   └── Index.vue
│   │   ├── Users/
│   │   │   ├── Index.vue
│   │   │   ├── Create.vue
│   │   │   ├── Edit.vue
│   │   │   └── Show.vue
│   │   ├── Products/
│   │   │   ├── Index.vue
│   │   │   ├── Create.vue
│   │   │   ├── Edit.vue
│   │   │   └── Show.vue
│   │   └── Orders/
│   │       ├── Index.vue
│   │       └── Show.vue
│   └── Auth/                       # Login/registro (compartilhado)
│       ├── Login.vue
│       └── Register.vue
├── router/
│   ├── index.ts
│   ├── central.ts                  # Rotas do Central
│   ├── tenant.ts                   # Rotas do Tenant
│   └── auth.ts
├── services/
│   ├── http.ts
│   ├── authService.ts              # Compartilhado
│   ├── central/
│   │   ├── tenantService.ts
│   │   └── planService.ts
│   └── tenant/
│       ├── userService.ts
│       └── productService.ts
├── stores/
│   ├── auth.ts
│   ├── tenantContext.ts            # Tenant atual (para o Tenant)
│   └── notification.ts
├── types/
│   ├── central/
│   │   ├── tenant.ts
│   │   └── plan.ts
│   ├── tenant/
│   │   ├── user.ts
│   │   └── product.ts
│   └── api.ts
└── utils/
    ├── format.ts
    ├── validators.ts
    ├── string.ts
    └── index.ts
```

---

## 🧩 Camadas e Responsabilidades

| Camada         | Responsabilidade                              | Contexto             |
| -------------- | --------------------------------------------- | -------------------- |
| **Pages**      | Colar componentes, chamar composable          | `Central/` ou `Tenant/` |
| **Components** | UI reutilizável                               | `common/`, `central/` ou `tenant/` |
| **Composables**| Lógica de estado + regras + Service           | `shared/`, `central/` ou `tenant/` |
| **Services**   | HTTP puro                                     | `central/`, `tenant/` ou raiz |
| **Stores**     | Estado global                                 | Raiz                 |
| **Constants**  | Valores fixos e enums                         | Raiz (`routes/` subdividido) |
| **Utils**      | Funções puras                                 | Raiz                 |
| **Types**      | Tipagens                                      | `central/` ou `tenant/` |

---

## 📏 Regras por Camada

### 1. Pages (`Central/` e `Tenant/`)

✅ **DEVE:**
- Estar dentro de `pages/Central/{Domínio}/` ou `pages/Tenant/{Domínio}/`
- Ter **4 arquivos por domínio**: `Index.vue`, `Create.vue`, `Edit.vue`, `Show.vue`
- Conter apenas template + chamada do composable
- Ter no máximo **80 linhas**

❌ **NÃO DEVE:**
- Conter `ref`, `reactive`, `computed`
- Fazer `axios.get()` ou `fetch()`
- Importar de Pages de outro contexto
- Conter regra de negócio

---

### 2. Components

✅ **DEVE:**
- **Comum** → `components/common/` (ex: `AppButton.vue`)
- **Específico do Central** → `components/central/{domínio}/`
- **Específico do Tenant** → `components/tenant/{domínio}/`
- Receber `props` tipadas e emitir `events`

❌ **NÃO DEVE:**
- Fazer chamadas HTTP
- Acessar `useRouter()` para navegação (emitir evento)
- Ser importado por Pages de contexto diferente

---

### 3. Composables

✅ **DEVE:**
- **Compartilhado** → `composables/shared/`
- **Central** → `composables/central/`
- **Tenant** → `composables/tenant/`
- Prefixo `use` — `useTenants()`, `useUsers()`
- Chamar Services (nunca Axios direto)

❌ **NÃO DEVE:**
- Renderizar UI
- Importar componentes `.vue`
- Ser compartilhado entre contextos sem mover para `shared/`

---

### 4. Services

✅ **DEVE:**
- **Central** → `services/central/`
- **Tenant** → `services/tenant/`
- **Compartilhado** → raiz `services/`
- Retornar `Promise<T>` tipado

❌ **NÃO DEVE:**
- Conter regra de negócio
- Guardar estado

---

### 5. Constants

✅ **DEVE:**
- Separar rotas: `routes/central.ts` e `routes/tenant.ts`
- Prefixar rotas corretamente: `/central/*` e `/app/*`
- Ter barrel export em `routes/index.ts`

❌ **NÃO DEVE:**
- Misturar rotas dos dois contextos no mesmo arquivo

---

### 6. Utils

✅ **DEVE:**
- Ser compartilhado (raiz `utils/`)
- Conter **funções puras**
- Ser tipado

❌ **NÃO DEVE:**
- Acessar estado global
- Fazer chamadas HTTP

---

## 📛 Convenções de Nomenclatura

| Tipo                | Padrão                         | Exemplo                              |
| ------------------- | ------------------------------ | ------------------------------------ |
| Page (Central)      | `pages/Central/{Domain}/Index.vue` | `pages/Central/Tenants/Index.vue` |
| Page (Tenant)       | `pages/Tenant/{Domain}/Index.vue`  | `pages/Tenant/Users/Index.vue`    |
| Component comum     | `App{Name}.vue`                | `AppButton.vue`                      |
| Component Central   | `{Domain}{Name}.vue`           | `components/central/tenants/TenantForm.vue` |
| Component Tenant    | `{Domain}{Name}.vue`           | `components/tenant/users/UserForm.vue` |
| Composable Central  | `use{Name}.ts`                 | `composables/central/useTenants.ts`  |
| Composable Tenant   | `use{Name}.ts`                 | `composables/tenant/useUsers.ts`     |
| Service Central     | `{name}Service.ts`             | `services/central/tenantService.ts`  |
| Service Tenant      | `{name}Service.ts`             | `services/tenant/userService.ts`     |
| Constant            | `SCREAMING_SNAKE_CASE`         | `ROUTES_CENTRAL.TENANTS.INDEX`       |

---

## 💻 Exemplos de Código

### 🔹 Constants — Rotas separadas

```ts
// src/constants/routes/central.ts
export const ROUTES_CENTRAL = {
  PREFIX: '/central',
  DASHBOARD: '/central/dashboard',
  TENANTS: {
    INDEX: '/central/tenants',
    CREATE: '/central/tenants/create',
    EDIT: (id: number | string) => `/central/tenants/${id}/edit`,
    SHOW: (id: number | string) => `/central/tenants/${id}`,
  },
  PLANS: {
    INDEX: '/central/plans',
    CREATE: '/central/plans/create',
    EDIT: (id: number | string) => `/central/plans/${id}/edit`,
    SHOW: (id: number | string) => `/central/plans/${id}`,
  },
} as const

export const ROUTE_NAMES_CENTRAL = {
  DASHBOARD: 'central.dashboard',
  TENANTS: {
    INDEX: 'central.tenants.index',
    CREATE: 'central.tenants.create',
    EDIT: 'central.tenants.edit',
    SHOW: 'central.tenants.show',
  },
  PLANS: {
    INDEX: 'central.plans.index',
    CREATE: 'central.plans.create',
    EDIT: 'central.plans.edit',
    SHOW: 'central.plans.show',
  },
} as const
```

```ts
// src/constants/routes/tenant.ts
export const ROUTES_TENANT = {
  PREFIX: '/app',
  DASHBOARD: '/app/dashboard',
  USERS: {
    INDEX: '/app/users',
    CREATE: '/app/users/create',
    EDIT: (id: number | string) => `/app/users/${id}/edit`,
    SHOW: (id: number | string) => `/app/users/${id}`,
  },
  PRODUCTS: {
    INDEX: '/app/products',
    CREATE: '/app/products/create',
    EDIT: (id: number | string) => `/app/products/${id}/edit`,
    SHOW: (id: number | string) => `/app/products/${id}`,
  },
} as const

export const ROUTE_NAMES_TENANT = {
  DASHBOARD: 'tenant.dashboard',
  USERS: {
    INDEX: 'tenant.users.index',
    CREATE: 'tenant.users.create',
    EDIT: 'tenant.users.edit',
    SHOW: 'tenant.users.show',
  },
  PRODUCTS: {
    INDEX: 'tenant.products.index',
    CREATE: 'tenant.products.create',
    EDIT: 'tenant.products.edit',
    SHOW: 'tenant.products.show',
  },
} as const
```

```ts
// src/constants/routes/index.ts
export * from './central'
export * from './tenant'
export * from './auth'
```

```ts
// src/constants/api.ts
export const API_ENDPOINTS = {
  AUTH: {
    LOGIN: '/auth/login',
    LOGOUT: '/auth/logout',
    ME: '/auth/me',
  },
  CENTRAL: {
    TENANTS: '/central/tenants',
    PLANS: '/central/plans',
    INVOICES: '/central/invoices',
  },
  TENANT: {
    USERS: '/app/users',
    PRODUCTS: '/app/products',
    ORDERS: '/app/orders',
  },
} as const
```

---

### 🔹 Types — Separados por contexto

```ts
// src/types/central/tenant.ts
export interface Tenant {
  id: number
  name: string
  domain: string
  plan_id: number
  status: 'active' | 'inactive' | 'suspended'
  created_at: string
}

export interface CreateTenantPayload {
  name: string
  domain: string
  plan_id: number
  admin_email: string
}
```

```ts
// src/types/tenant/user.ts
export interface User {
  id: number
  name: string
  email: string
  role: 'admin' | 'manager' | 'user'
  status: 'active' | 'inactive'
  created_at: string
}

export interface CreateUserPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
  role: 'admin' | 'manager' | 'user'
}
```

---

### 🔹 Services — Separados por contexto

```ts
// src/services/central/tenantService.ts
import { http } from '../http'
import { API_ENDPOINTS } from '@/constants'
import type {
  Tenant,
  CreateTenantPayload,
} from '@/types/central/tenant'
import type { ApiResponse, PaginatedResponse } from '@/types/api'

export const tenantService = {
  async list(params?: { page?: number }) {
    const { data } = await http.get<PaginatedResponse<Tenant>>(
      API_ENDPOINTS.CENTRAL.TENANTS,
      { params }
    )
    return data
  },

  async find(id: number | string) {
    const { data } = await http.get<ApiResponse<Tenant>>(
      `${API_ENDPOINTS.CENTRAL.TENANTS}/${id}`
    )
    return data.data
  },

  async create(payload: CreateTenantPayload) {
    const { data } = await http.post<ApiResponse<Tenant>>(
      API_ENDPOINTS.CENTRAL.TENANTS,
      payload
    )
    return data.data
  },

  async update(id: number | string, payload: Partial<CreateTenantPayload>) {
    const { data } = await http.put<ApiResponse<Tenant>>(
      `${API_ENDPOINTS.CENTRAL.TENANTS}/${id}`,
      payload
    )
    return data.data
  },

  async remove(id: number | string) {
    await http.delete(`${API_ENDPOINTS.CENTRAL.TENANTS}/${id}`)
  },
}
```

```ts
// src/services/tenant/userService.ts
import { http } from '../http'
import { API_ENDPOINTS } from '@/constants'
import type { User, CreateUserPayload } from '@/types/tenant/user'
import type { ApiResponse, PaginatedResponse } from '@/types/api'

export const userService = {
  async list(params?: { page?: number }) {
    const { data } = await http.get<PaginatedResponse<User>>(
      API_ENDPOINTS.TENANT.USERS,
      { params }
    )
    return data
  },

  async find(id: number | string) {
    const { data } = await http.get<ApiResponse<User>>(
      `${API_ENDPOINTS.TENANT.USERS}/${id}`
    )
    return data.data
  },

  async create(payload: CreateUserPayload) {
    const { data } = await http.post<ApiResponse<User>>(
      API_ENDPOINTS.TENANT.USERS,
      payload
    )
    return data.data
  },

  async update(id: number | string, payload: Partial<CreateUserPayload>) {
    const { data } = await http.put<ApiResponse<User>>(
      `${API_ENDPOINTS.TENANT.USERS}/${id}`,
      payload
    )
    return data.data
  },

  async remove(id: number | string) {
    await http.delete(`${API_ENDPOINTS.TENANT.USERS}/${id}`)
  },
}
```

---

### 🔹 Composables — Separados por contexto

```ts
// src/composables/central/useTenants.ts
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { tenantService } from '@/services/central/tenantService'
import { MESSAGES, ROUTE_NAMES_CENTRAL } from '@/constants'
import { useNotificationStore } from '@/stores/notification'
import type { Tenant, CreateTenantPayload } from '@/types/central/tenant'

export function useTenants() {
  const router = useRouter()
  const notification = useNotificationStore()

  const tenants = ref<Tenant[]>([])
  const tenant = ref<Tenant | null>(null)
  const loading = ref(false)
  const pagination = ref({ page: 1, lastPage: 1, total: 0 })

  async function fetchTenants(page = 1) {
    loading.value = true
    try {
      const response = await tenantService.list({ page })
      tenants.value = response.data
      pagination.value = {
        page: response.meta.current_page,
        lastPage: response.meta.last_page,
        total: response.meta.total,
      }
    } catch {
      notification.error(MESSAGES.COMMON.ERROR)
    } finally {
      loading.value = false
    }
  }

  async function fetchTenant(id: number | string) {
    loading.value = true
    try {
      tenant.value = await tenantService.find(id)
    } catch {
      notification.error(MESSAGES.TENANT.NOT_FOUND)
      router.push({ name: ROUTE_NAMES_CENTRAL.TENANTS.INDEX })
    } finally {
      loading.value = false
    }
  }

  async function createTenant(payload: CreateTenantPayload) {
    loading.value = true
    try {
      await tenantService.create(payload)
      notification.success(MESSAGES.TENANT.CREATED)
      router.push({ name: ROUTE_NAMES_CENTRAL.TENANTS.INDEX })
    } catch {
      notification.error(MESSAGES.COMMON.ERROR)
    } finally {
      loading.value = false
    }
  }

  async function updateTenant(
    id: number | string,
    payload: Partial<CreateTenantPayload>
  ) {
    loading.value = true
    try {
      await tenantService.update(id, payload)
      notification.success(MESSAGES.TENANT.UPDATED)
      router.push({ name: ROUTE_NAMES_CENTRAL.TENANTS.INDEX })
    } catch {
      notification.error(MESSAGES.COMMON.ERROR)
    } finally {
      loading.value = false
    }
  }

  async function deleteTenant(id: number | string) {
    if (!confirm(MESSAGES.COMMON.CONFIRM_DELETE)) return
    try {
      await tenantService.remove(id)
      notification.success(MESSAGES.TENANT.DELETED)
      fetchTenants(pagination.value.page)
    } catch {
      notification.error(MESSAGES.COMMON.ERROR)
    }
  }

  return {
    tenants,
    tenant,
    loading,
    pagination,
    fetchTenants,
    fetchTenant,
    createTenant,
    updateTenant,
    deleteTenant,
  }
}
```

```ts
// src/composables/tenant/useUsers.ts
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { userService } from '@/services/tenant/userService'
import { MESSAGES, ROUTE_NAMES_TENANT } from '@/constants'
import { useNotificationStore } from '@/stores/notification'
import type { User, CreateUserPayload } from '@/types/tenant/user'

export function useUsers() {
  const router = useRouter()
  const notification = useNotificationStore()

  const users = ref<User[]>([])
  const user = ref<User | null>(null)
  const loading = ref(false)
  const pagination = ref({ page: 1, lastPage: 1, total: 0 })

  async function fetchUsers(page = 1) {
    loading.value = true
    try {
      const response = await userService.list({ page })
      users.value = response.data
      pagination.value = {
        page: response.meta.current_page,
        lastPage: response.meta.last_page,
        total: response.meta.total,
      }
    } catch {
      notification.error(MESSAGES.COMMON.ERROR)
    } finally {
      loading.value = false
    }
  }

  async function fetchUser(id: number | string) {
    loading.value = true
    try {
      user.value = await userService.find(id)
    } catch {
      notification.error(MESSAGES.USER.NOT_FOUND)
      router.push({ name: ROUTE_NAMES_TENANT.USERS.INDEX })
    } finally {
      loading.value = false
    }
  }

  async function createUser(payload: CreateUserPayload) {
    loading.value = true
    try {
      await userService.create(payload)
      notification.success(MESSAGES.USER.CREATED)
      router.push({ name: ROUTE_NAMES_TENANT.USERS.INDEX })
    } catch {
      notification.error(MESSAGES.COMMON.ERROR)
    } finally {
      loading.value = false
    }
  }

  async function updateUser(
    id: number | string,
    payload: Partial<CreateUserPayload>
  ) {
    loading.value = true
    try {
      await userService.update(id, payload)
      notification.success(MESSAGES.USER.UPDATED)
      router.push({ name: ROUTE_NAMES_TENANT.USERS.INDEX })
    } catch {
      notification.error(MESSAGES.COMMON.ERROR)
    } finally {
      loading.value = false
    }
  }

  async function deleteUser(id: number | string) {
    if (!confirm(MESSAGES.COMMON.CONFIRM_DELETE)) return
    try {
      await userService.remove(id)
      notification.success(MESSAGES.USER.DELETED)
      fetchUsers(pagination.value.page)
    } catch {
      notification.error(MESSAGES.COMMON.ERROR)
    }
  }

  return {
    users,
    user,
    loading,
    pagination,
    fetchUsers,
    fetchUser,
    createUser,
    updateUser,
    deleteUser,
  }
}
```

---

### 🔹 Pages — Central

```vue
<!-- src/pages/Central/Tenants/Index.vue -->
<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useTenants } from '@/composables/central/useTenants'
import { ROUTE_NAMES_CENTRAL } from '@/constants'
import TenantTable from '@/components/central/tenants/TenantTable.vue'
import AppButton from '@/components/common/AppButton.vue'

const router = useRouter()
const { tenants, loading, fetchTenants, deleteTenant } = useTenants()

onMounted(() => fetchTenants())
</script>

<template>
  <div>
    <header class="flex justify-between mb-4">
      <h1>Tenants</h1>
      <AppButton @click="router.push({ name: ROUTE_NAMES_CENTRAL.TENANTS.CREATE })">
        Novo Tenant
      </AppButton>
    </header>

    <TenantTable
      :tenants="tenants"
      :loading="loading"
      @delete="deleteTenant"
      @edit="(id) => router.push({ name: ROUTE_NAMES_CENTRAL.TENANTS.EDIT, params: { id } })"
      @show="(id) => router.push({ name: ROUTE_NAMES_CENTRAL.TENANTS.SHOW, params: { id } })"
    />
  </div>
</template>
```

```vue
<!-- src/pages/Central/Tenants/Create.vue -->
<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useTenants } from '@/composables/central/useTenants'
import { ROUTE_NAMES_CENTRAL } from '@/constants'
import TenantForm from '@/components/central/tenants/TenantForm.vue'

const router = useRouter()
const { loading, createTenant } = useTenants()
</script>

<template>
  <div>
    <h1>Novo Tenant</h1>
    <TenantForm
      :loading="loading"
      @submit="createTenant"
      @cancel="router.push({ name: ROUTE_NAMES_CENTRAL.TENANTS.INDEX })"
    />
  </div>
</template>
```

```vue
<!-- src/pages/Central/Tenants/Edit.vue -->
<script setup lang="ts">
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useTenants } from '@/composables/central/useTenants'
import { ROUTE_NAMES_CENTRAL } from '@/constants'
import TenantForm from '@/components/central/tenants/TenantForm.vue'

const route = useRoute()
const router = useRouter()
const { tenant, loading, fetchTenant, updateTenant } = useTenants()

onMounted(() => fetchTenant(route.params.id as string))
</script>

<template>
  <div>
    <h1>Editar Tenant</h1>
    <TenantForm
      v-if="tenant"
      :model-value="tenant"
      :loading="loading"
      @submit="(payload) => updateTenant(route.params.id as string, payload)"
      @cancel="router.push({ name: ROUTE_NAMES_CENTRAL.TENANTS.INDEX })"
    />
  </div>
</template>
```

```vue
<!-- src/pages/Central/Tenants/Show.vue -->
<script setup lang="ts">
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useTenants } from '@/composables/central/useTenants'
import { ROUTE_NAMES_CENTRAL } from '@/constants'
import AppButton from '@/components/common/AppButton.vue'
import { formatDate } from '@/utils'

const route = useRoute()
const router = useRouter()
const { tenant, loading, fetchTenant } = useTenants()

onMounted(() => fetchTenant(route.params.id as string))
</script>

<template>
  <div v-if="!loading && tenant">
    <h1>{{ tenant.name }}</h1>
    <p>Domínio: {{ tenant.domain }}</p>
    <p>Status: {{ tenant.status }}</p>
    <p>Criado em: {{ formatDate(tenant.created_at) }}</p>

    <AppButton
      @click="router.push({ name: ROUTE_NAMES_CENTRAL.TENANTS.EDIT, params: { id: tenant.id } })"
    >
      Editar
    </AppButton>
  </div>
</template>
```

---

### 🔹 Pages — Tenant

```vue
<!-- src/pages/Tenant/Users/Index.vue -->
<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useUsers } from '@/composables/tenant/useUsers'
import { ROUTE_NAMES_TENANT } from '@/constants'
import UserTable from '@/components/tenant/users/UserTable.vue'
import AppButton from '@/components/common/AppButton.vue'

const router = useRouter()
const { users, loading, fetchUsers, deleteUser } = useUsers()

onMounted(() => fetchUsers())
</script>

<template>
  <div>
    <header class="flex justify-between mb-4">
      <h1>Usuários</h1>
      <AppButton @click="router.push({ name: ROUTE_NAMES_TENANT.USERS.CREATE })">
        Novo Usuário
      </AppButton>
    </header>

    <UserTable
      :users="users"
      :loading="loading"
      @delete="deleteUser"
      @edit="(id) => router.push({ name: ROUTE_NAMES_TENANT.USERS.EDIT, params: { id } })"
      @show="(id) => router.push({ name: ROUTE_NAMES_TENANT.USERS.SHOW, params: { id } })"
    />
  </div>
</template>
```

```vue
<!-- src/pages/Tenant/Users/Create.vue -->
<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useUsers } from '@/composables/tenant/useUsers'
import { ROUTE_NAMES_TENANT } from '@/constants'
import UserForm from '@/components/tenant/users/UserForm.vue'

const router = useRouter()
const { loading, createUser } = useUsers()
</script>

<template>
  <div>
    <h1>Novo Usuário</h1>
    <UserForm
      :loading="loading"
      @submit="createUser"
      @cancel="router.push({ name: ROUTE_NAMES_TENANT.USERS.INDEX })"
    />
  </div>
</template>
```

```vue
<!-- src/pages/Tenant/Users/Edit.vue -->
<script setup lang="ts">
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useUsers } from '@/composables/tenant/useUsers'
import { ROUTE_NAMES_TENANT } from '@/constants'
import UserForm from '@/components/tenant/users/UserForm.vue'

const route = useRoute()
const router = useRouter()
const { user, loading, fetchUser, updateUser } = useUsers()

onMounted(() => fetchUser(route.params.id as string))
</script>

<template>
  <div>
    <h1>Editar Usuário</h1>
    <UserForm
      v-if="user"
      :model-value="user"
      :loading="loading"
      @submit="(payload) => updateUser(route.params.id as string, payload)"
      @cancel="router.push({ name: ROUTE_NAMES_TENANT.USERS.INDEX })"
    />
  </div>
</template>
```

```vue
<!-- src/pages/Tenant/Users/Show.vue -->
<script setup lang="ts">
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useUsers } from '@/composables/tenant/useUsers'
import { ROUTE_NAMES_TENANT } from '@/constants'
import AppButton from '@/components/common/AppButton.vue'
import { formatDate } from '@/utils'

const route = useRoute()
const router = useRouter()
const { user, loading, fetchUser } = useUsers()

onMounted(() => fetchUser(route.params.id as string))
</script>

<template>
  <div v-if="!loading && user">
    <h1>{{ user.name }}</h1>
    <p>{{ user.email }}</p>
    <p>Papel: {{ user.role }}</p>
    <p>Criado em: {{ formatDate(user.created_at) }}</p>

    <AppButton
      @click="router.push({ name: ROUTE_NAMES_TENANT.USERS.EDIT, params: { id: user.id } })"
    >
      Editar
    </AppButton>
  </div>
</template>
```

---

### 🔹 Router — Separado por contexto

```ts
// src/router/central.ts
import type { RouteRecordRaw } from 'vue-router'
import { ROUTES_CENTRAL, ROUTE_NAMES_CENTRAL } from '@/constants'
import CentralLayout from '@/layouts/CentralLayout.vue'

export const centralRoutes: RouteRecordRaw[] = [
  {
    path: ROUTES_CENTRAL.PREFIX,
    component: CentralLayout,
    meta: { requiresAuth: true, context: 'central' },
    children: [
      {
        path: 'dashboard',
        name: ROUTE_NAMES_CENTRAL.DASHBOARD,
        component: () => import('@/pages/Central/Dashboard/Index.vue'),
      },
      {
        path: 'tenants',
        name: ROUTE_NAMES_CENTRAL.TENANTS.INDEX,
        component: () => import('@/pages/Central/Tenants/Index.vue'),
      },
      {
        path: 'tenants/create',
        name: ROUTE_NAMES_CENTRAL.TENANTS.CREATE,
        component: () => import('@/pages/Central/Tenants/Create.vue'),
      },
      {
        path: 'tenants/:id/edit',
        name: ROUTE_NAMES_CENTRAL.TENANTS.EDIT,
        component: () => import('@/pages/Central/Tenants/Edit.vue'),
      },
      {
        path: 'tenants/:id',
        name: ROUTE_NAMES_CENTRAL.TENANTS.SHOW,
        component: () => import('@/pages/Central/Tenants/Show.vue'),
      },
    ],
  },
]
```

```ts
// src/router/tenant.ts
import type { RouteRecordRaw } from 'vue-router'
import { ROUTES_TENANT, ROUTE_NAMES_TENANT } from '@/constants'
import TenantLayout from '@/layouts/TenantLayout.vue'

export const tenantRoutes: RouteRecordRaw[] = [
  {
    path: ROUTES_TENANT.PREFIX,
    component: TenantLayout,
    meta: { requiresAuth: true, context: 'tenant' },
    children: [
      {
        path: 'dashboard',
        name: ROUTE_NAMES_TENANT.DASHBOARD,
        component: () => import('@/pages/Tenant/Dashboard/Index.vue'),
      },
      {
        path: 'users',
        name: ROUTE_NAMES_TENANT.USERS.INDEX,
        component: () => import('@/pages/Tenant/Users/Index.vue'),
      },
      {
        path: 'users/create',
        name: ROUTE_NAMES_TENANT.USERS.CREATE,
        component: () => import('@/pages/Tenant/Users/Create.vue'),
      },
      {
        path: 'users/:id/edit',
        name: ROUTE_NAMES_TENANT.USERS.EDIT,
        component: () => import('@/pages/Tenant/Users/Edit.vue'),
      },
      {
        path: 'users/:id',
        name: ROUTE_NAMES_TENANT.USERS.SHOW,
        component: () => import('@/pages/Tenant/Users/Show.vue'),
      },
    ],
  },
]
```

```ts
// src/router/index.ts
import { createRouter, createWebHistory } from 'vue-router'
import { centralRoutes } from './central'
import { tenantRoutes } from './tenant'
import { authRoutes } from './auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    ...authRoutes,
    ...centralRoutes,
    ...tenantRoutes,
  ],
})

export default router
```

---

## 🚫 Regras Proibidas (Anti-patterns)

| ❌ Anti-pattern                                        | ✅ Correto                                            |
| ------------------------------------------------------ | ---------------------------------------------------- |
| Page em `pages/Users/` (sem contexto)                  | `pages/Central/...` ou `pages/Tenant/...`            |
| `pages/Central/` importando de `pages/Tenant/`         | Manter isolado; mover para `common/` se compartilhado |
| Composable único para os 2 contextos                   | Criar em `shared/` ou duplicar por contexto          |
| Rota `/users` sem prefixo `/central` ou `/app`         | Prefixar corretamente                                 |
| `ROUTES.USERS.INDEX` global                            | `ROUTES_CENTRAL.TENANTS.INDEX` ou `ROUTES_TENANT.USERS.INDEX` |
| Service único para os 2 contextos                      | Separar em `services/central/` e `services/tenant/`  |
| Types compartilhados entre contextos                   | Separar em `types/central/` e `types/tenant/`        |

---

## ✅ Checklist de Pull Request

### Pages
- [ ] Está em `pages/Central/{Domínio}/` ou `pages/Tenant/{Domínio}/`
- [ ] Existe `Index.vue`, `Create.vue`, `Edit.vue`, `Show.vue`
- [ ] Sem `ref`/`reactive`/`computed`
- [ ] Sem chamada HTTP direta
- [ ] < 80 linhas
- [ ] Não importa Pages do contexto oposto

### Components
- [ ] Comum em `common/`, específico em `central/` ou `tenant/`
- [ ] Recebe `props` tipadas
- [ ] Emite `events`

### Composables
- [ ] Em `shared/`, `central/` ou `tenant/`
- [ ] Prefixo `use`
- [ ] Chama Service, nunca `axios` direto

### Services
- [ ] Em `services/central/` ou `services/tenant/`
- [ ] Compartilhado → raiz `services/`
- [ ] Retorna `Promise<T>` tipado

### Constants
- [ ] Rotas separadas em `routes/central.ts` e `routes/tenant.ts`
- [ ] Prefixos corretos (`/central/*`, `/app/*`)
- [ ] Nenhuma string mágica no código

### Utils
- [ ] Funções puras
- [ ] Tipadas
- [ ] Compartilhadas na raiz `utils/`

---

## 🧪 Testes

| Camada               | Ferramenta                    | Foco                        |
| -------------------- | ----------------------------- | --------------------------- |
| Utils                | Vitest                        | Entrada/saída               |
| Composables (shared) | Vitest + `@vue/test-utils`    | Estado e services           |
| Composables Central  | Vitest                        | Estado central              |
| Composables Tenant   | Vitest                        | Estado tenant               |
| Components           | Vitest + `@vue/test-utils`    | Props, emits                |
| Pages Central        | Cypress / Playwright          | E2E fluxo admin             |
| Pages Tenant         | Cypress / Playwright          | E2E fluxo cliente           |

---

## 📚 Referências

- [Vue 3 Composition API](https://vuejs.org/guide/extras/composition-api-faq.html)
- [Pinia](https://pinia.vuejs.org/)
- [Vue Router](https://router.vuejs.org/)
- [Vitest](https://vitest.dev/)
- Backend: [`docs/ARCHITECTURE.md`](./ARCHITECTURE.md)

---

**Última atualização:** {{ data }}
**Mantido por:** Time de Engenharia Frontend