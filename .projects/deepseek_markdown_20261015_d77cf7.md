# 🎨 Padrão de Arquitetura — Frontend

> Documento normativo do frontend. Complementa o `docs/ARCHITECTURE.md` (backend).
> **Obrigatório** para todos os desenvolvedores.

---

## 📑 Índice

1. [Visão Geral](#-visão-geral)
2. [Stack](#-stack)
3. [Estrutura de Pastas](#-estrutura-de-pastas)
4. [Camadas e Responsabilidades](#-camadas-e-responsabilidades)
5. [Regras por Camada](#-regras-por-camada)
6. [Convenções de Nomenclatura](#-convenções-de-nomenclatura)
7. [Exemplos de Código](#-exemplos-de-código)
8. [Regras Proibidas (Anti-patterns)](#-regras-proibidas-anti-patterns)
9. [Checklist de Pull Request](#-checklist-de-pull-request)

---

## 🎯 Visão Geral

O frontend segue uma arquitetura **modular por domínio**, com forte reuso de **Componentes** e separação clara entre:

- **Pages** — apenas orquestram componentes e chamam composables
- **Components** — blocos reutilizáveis de UI (burros ou com props)
- **Composables** — toda a lógica de estado, regras e chamadas de API
- **Services** — comunicação HTTP (Axios/Fetch)
- **Constants** — valores fixos, enums, rotas, mensagens
- **Utils** — funções puras e helpers
- **Types** — tipagens TypeScript (interfaces, types)

### Princípios Fundamentais

- **Zero redundância** — se repete 2×, extraia para Component/Composable/Util
- **Pages magras** — apenas colam componentes
- **Lógica em Composables** — nunca dentro de `.vue` de Page
- **Componentes puros** — recebem `props`, emitem `events`
- **Constants centralizadas** — nunca hardcode strings mágicas
- **Tipagem obrigatória** — TypeScript em tudo

---

## 🛠️ Stack

| Tecnologia        | Uso                          |
| ----------------- | ---------------------------- |
| Vue 3             | Framework (Composition API)  |
| TypeScript        | Tipagem estática             |
| Pinia             | Estado global                |
| Vue Router        | Roteamento                   |
| Axios             | HTTP client                   |
| Tailwind / SCSS   | Estilização                  |
| Vite              | Build tool                   |
| Vitest            | Testes                       |

---

## 🗂️ Estrutura de Pastas

```
src/
├── assets/                    # Imagens, fontes, ícones
├── components/
│   ├── common/                # Botões, Inputs, Modais (genéricos)
│   │   ├── AppButton.vue
│   │   ├── AppInput.vue
│   │   ├── AppModal.vue
│   │   ├── AppTable.vue
│   │   └── AppPagination.vue
│   └── domain/                # Componentes específicos do domínio
│       └── users/
│           ├── UserForm.vue
│           ├── UserCard.vue
│           └── UserTable.vue
├── composables/
│   ├── useUsers.ts            # Lógica da listagem/criação/edição
│   ├── useAuth.ts
│   └── useNotification.ts
├── constants/
│   ├── routes.ts              # Nomes e paths de rotas
│   ├── api.ts                 # Endpoints
│   ├── messages.ts            # Mensagens padronizadas
│   ├── status.ts              # Enums (status, roles)
│   └── index.ts               # Barrel export
├── layouts/
│   ├── DefaultLayout.vue
│   └── AuthLayout.vue
├── pages/
│   └── users/
│       ├── Index.vue          # Listagem
│       ├── Create.vue         # Cadastro
│       ├── Edit.vue           # Edição
│       └── Show.vue           # Detalhes
├── router/
│   └── index.ts
├── services/
│   ├── http.ts                # Axios instance
│   ├── userService.ts
│   └── authService.ts
├── stores/                    # Pinia
│   ├── auth.ts
│   └── notification.ts
├── types/
│   ├── user.ts
│   └── api.ts
└── utils/
    ├── format.ts              # Formatação (data, moeda, CPF)
    ├── validators.ts          # Validações reutilizáveis
    ├── string.ts              # Helpers de string
    ├── storage.ts             # LocalStorage helpers
    └── index.ts               # Barrel export
```

---

## 🧩 Camadas e Responsabilidades

| Camada         | Responsabilidade                                    | ❌ Não pode                          |
| -------------- | --------------------------------------------------- | ------------------------------------ |
| **Pages**      | Colar componentes, chamar composable                | Lógica, chamadas HTTP diretas        |
| **Components** | UI reutilizável, receber `props`, emitir `events`   | Chamar API, acessar store direto     |
| **Composables**| Lógica de estado + regras + integração com Services | Renderizar UI                        |
| **Services**   | HTTP puro (Axios/Fetch)                             | Regra de negócio, estado             |
| **Stores**     | Estado global (Pinia)                               | Chamadas HTTP diretas (usar Service) |
| **Constants**  | Valores fixos e enums                               | Lógica                               |
| **Utils**      | Funções puras e helpers                             | Estado, chamadas HTTP                |
| **Types**      | Tipagens, interfaces                                | Implementação                        |

---

## 📏 Regras por Camada

### 1. Pages

✅ **DEVE:**
- Ter **4 arquivos por domínio**: `Index.vue`, `Create.vue`, `Edit.vue`, `Show.vue`
- Conter apenas **template + chamada do composable**
- Ter no máximo **80 linhas**
- Usar componentes de `components/domain/` e `components/common/`

❌ **NÃO DEVE:**
- Conter `ref`, `reactive`, `computed` (isso é do composable)
- Fazer `axios.get()` ou `fetch()`
- Conter regra de negócio
- Ter CSS extenso (usar Tailwind/classes)

---

### 2. Components

✅ **DEVE:**
- Ser **puro** — recebe `props`, emite `emits`
- Ser reutilizável em 2+ lugares (se for usar 1×, ainda é válido se separa responsabilidade)
- Ter nome claro: `App{Name}.vue` (comum) ou `{Domain}{Name}.vue` (domínio)
- Ser tipado (`defineProps<Props>()`)

❌ **NÃO DEVE:**
- Fazer chamadas HTTP
- Acessar `useRouter()` para navegação direta (emitir evento)
- Conter regra de negócio pesada
- Depender de um composable específico de página

---

### 3. Composables

✅ **DEVE:**
- Conter **toda** a lógica da página (estado, validação, submit)
- Retornar refs/computed/methods tipados
- Ser prefixado com `use` — `useUsers()`
- Ser testável isoladamente
- Chamar Services (nunca Axios direto)

❌ **NÃO DEVE:**
- Renderizar UI
- Importar componentes `.vue`
- Acessar DOM diretamente

---

### 4. Services

✅ **DEVE:**
- Centralizar chamadas HTTP
- Retornar `Promise<T>` tipado
- Usar a instância `http.ts`
- Ter sufixo `Service` — `userService.ts`

❌ **NÃO DEVE:**
- Conter regra de negócio
- Guardar estado
- Formatar dados para UI

---

### 5. Stores (Pinia)

✅ **DEVE:**
- Guardar **estado global** (auth, notifications, tema)
- Ser acessado por composables, não por components diretamente

❌ **NÃO DEVE:**
- Substituir composables
- Ser usado para estado local de página

---

### 6. Constants

✅ **DEVE:**
- Conter **todos** os valores fixos: rotas, endpoints, mensagens, enums
- Ser tipado (`as const`)
- Ter um `index.ts` com barrel export

❌ **NÃO DEVE:**
- Conter lógica (nem `if`)
- Conter chamadas HTTP

---

### 7. Utils

✅ **DEVE:**
- Conter **funções puras** (mesma entrada → mesma saída)
- Ser tipado
- Ser testável
- Ter um `index.ts` com barrel export

❌ **NÃO DEVE:**
- Acessar estado global
- Fazer chamadas HTTP
- Modificar o DOM

---

## 📛 Convenções de Nomenclatura

| Tipo              | Padrão                          | Exemplo                    |
| ----------------- | ------------------------------- | -------------------------- |
| Page              | `{Index\|Create\|Edit\|Show}.vue` | `Index.vue`              |
| Component comum   | `App{Name}.vue`                 | `AppButton.vue`            |
| Component domínio | `{Domain}{Name}.vue`            | `UserForm.vue`             |
| Composable        | `use{Name}.ts`                  | `useUsers.ts`              |
| Service           | `{name}Service.ts`              | `userService.ts`           |
| Store             | `use{Name}Store.ts`             | `useAuthStore.ts`          |
| Constant          | `SCREAMING_SNAKE_CASE`          | `API_ENDPOINTS.USERS`      |
| Util              | `camelCase`                     | `formatCurrency()`         |
| Type              | `PascalCase`                    | `User`, `ApiResponse<T>`   |

---

## 💻 Exemplos de Código

### 🔹 Constants

```ts
// src/constants/routes.ts
export const ROUTES = {
  HOME: '/',
  USERS: {
    INDEX: '/users',
    CREATE: '/users/create',
    EDIT: (id: number | string) => `/users/${id}/edit`,
    SHOW: (id: number | string) => `/users/${id}`,
  },
} as const

export const ROUTE_NAMES = {
  USERS: {
    INDEX: 'users.index',
    CREATE: 'users.create',
    EDIT: 'users.edit',
    SHOW: 'users.show',
  },
} as const
```

```ts
// src/constants/api.ts
export const API_ENDPOINTS = {
  USERS: '/users',
  AUTH: {
    LOGIN: '/auth/login',
    LOGOUT: '/auth/logout',
    ME: '/auth/me',
  },
} as const
```

```ts
// src/constants/messages.ts
export const MESSAGES = {
  USER: {
    CREATED: 'Usuário criado com sucesso!',
    UPDATED: 'Usuário atualizado com sucesso!',
    DELETED: 'Usuário removido com sucesso!',
    NOT_FOUND: 'Usuário não encontrado.',
  },
  COMMON: {
    ERROR: 'Ocorreu um erro. Tente novamente.',
    CONFIRM_DELETE: 'Tem certeza que deseja remover?',
  },
} as const
```

```ts
// src/constants/status.ts
export const USER_STATUS = {
  ACTIVE: 'active',
  INACTIVE: 'inactive',
  PENDING: 'pending',
} as const

export type UserStatus = (typeof USER_STATUS)[keyof typeof USER_STATUS]
```

```ts
// src/constants/index.ts
export * from './routes'
export * from './api'
export * from './messages'
export * from './status'
```

---

### 🔹 Types

```ts
// src/types/user.ts
export interface User {
  id: number
  name: string
  email: string
  status: 'active' | 'inactive' | 'pending'
  created_at: string
}

export interface CreateUserPayload {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export interface UpdateUserPayload {
  name?: string
  email?: string
  password?: string
}
```

```ts
// src/types/api.ts
export interface ApiResponse<T> {
  data: T
  message?: string
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}
```

---

### 🔹 Service

```ts
// src/services/http.ts
import axios from 'axios'

export const http = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  headers: { 'Content-Type': 'application/json' },
})

http.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})
```

```ts
// src/services/userService.ts
import { http } from './http'
import { API_ENDPOINTS } from '@/constants'
import type {
  User,
  CreateUserPayload,
  UpdateUserPayload,
} from '@/types/user'
import type { ApiResponse, PaginatedResponse } from '@/types/api'

export const userService = {
  async list(params?: { page?: number; per_page?: number }) {
    const { data } = await http.get<PaginatedResponse<User>>(
      API_ENDPOINTS.USERS,
      { params }
    )
    return data
  },

  async find(id: number | string) {
    const { data } = await http.get<ApiResponse<User>>(
      `${API_ENDPOINTS.USERS}/${id}`
    )
    return data.data
  },

  async create(payload: CreateUserPayload) {
    const { data } = await http.post<ApiResponse<User>>(
      API_ENDPOINTS.USERS,
      payload
    )
    return data.data
  },

  async update(id: number | string, payload: UpdateUserPayload) {
    const { data } = await http.put<ApiResponse<User>>(
      `${API_ENDPOINTS.USERS}/${id}`,
      payload
    )
    return data.data
  },

  async remove(id: number | string) {
    await http.delete(`${API_ENDPOINTS.USERS}/${id}`)
  },
}
```

---

### 🔹 Utils

```ts
// src/utils/format.ts
export const formatCurrency = (value: number): string =>
  new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value)

export const formatDate = (date: string | Date): string =>
  new Intl.DateTimeFormat('pt-BR').format(new Date(date))

export const formatCpf = (cpf: string): string =>
  cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
```

```ts
// src/utils/validators.ts
export const isEmail = (value: string): boolean =>
  /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)

export const isCpf = (value: string): boolean =>
  /^\d{3}\.\d{3}\.\d{3}-\d{2}$/.test(value)

export const isRequired = (value: unknown): boolean =>
  value !== null && value !== undefined && value !== ''
```

```ts
// src/utils/string.ts
export const capitalize = (value: string): string =>
  value.charAt(0).toUpperCase() + value.slice(1).toLowerCase()

export const truncate = (value: string, length = 50): string =>
  value.length > length ? `${value.slice(0, length)}...` : value
```

```ts
// src/utils/index.ts
export * from './format'
export * from './validators'
export * from './string'
```

---

### 🔹 Composable

```ts
// src/composables/useUsers.ts
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { userService } from '@/services/userService'
import { MESSAGES, ROUTE_NAMES } from '@/constants'
import { useNotificationStore } from '@/stores/notification'
import type { User, CreateUserPayload, UpdateUserPayload } from '@/types/user'

export function useUsers() {
  const router = useRouter()
  const notification = useNotificationStore()

  const users = ref<User[]>([])
  const user = ref<User | null>(null)
  const loading = ref(false)
  const pagination = ref({ page: 1, lastPage: 1, total: 0 })

  const isEmpty = computed(() => !loading.value && users.value.length === 0)

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
      router.push({ name: ROUTE_NAMES.USERS.INDEX })
    } finally {
      loading.value = false
    }
  }

  async function createUser(payload: CreateUserPayload) {
    loading.value = true
    try {
      await userService.create(payload)
      notification.success(MESSAGES.USER.CREATED)
      router.push({ name: ROUTE_NAMES.USERS.INDEX })
    } catch {
      notification.error(MESSAGES.COMMON.ERROR)
    } finally {
      loading.value = false
    }
  }

  async function updateUser(id: number | string, payload: UpdateUserPayload) {
    loading.value = true
    try {
      await userService.update(id, payload)
      notification.success(MESSAGES.USER.UPDATED)
      router.push({ name: ROUTE_NAMES.USERS.INDEX })
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
    isEmpty,
    fetchUsers,
    fetchUser,
    createUser,
    updateUser,
    deleteUser,
  }
}
```

---

### 🔹 Components (comuns e domínio)

```vue
<!-- src/components/common/AppButton.vue -->
<script setup lang="ts">
interface Props {
  variant?: 'primary' | 'secondary' | 'danger'
  loading?: boolean
  disabled?: boolean
}

withDefaults(defineProps<Props>(), {
  variant: 'primary',
  loading: false,
  disabled: false,
})

defineEmits<{ click: [] }>()
</script>

<template>
  <button
    :class="['btn', `btn--${variant}`]"
    :disabled="disabled || loading"
    @click="$emit('click')"
  >
    <span v-if="loading">Carregando...</span>
    <slot v-else />
  </button>
</template>
```

```vue
<!-- src/components/domain/users/UserForm.vue -->
<script setup lang="ts">
import { reactive, watch } from 'vue'
import AppInput from '@/components/common/AppInput.vue'
import AppButton from '@/components/common/AppButton.vue'
import type { User, CreateUserPayload } from '@/types/user'

interface Props {
  modelValue?: User | null
  loading?: boolean
}

const props = defineProps<Props>()

const emit = defineEmits<{
  submit: [payload: CreateUserPayload]
  cancel: []
}>()

const form = reactive<CreateUserPayload>({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

watch(
  () => props.modelValue,
  (user) => {
    if (user) {
      form.name = user.name
      form.email = user.email
    }
  },
  { immediate: true }
)

function handleSubmit() {
  emit('submit', { ...form })
}
</script>

<template>
  <form @submit.prevent="handleSubmit">
    <AppInput v-model="form.name" label="Nome" required />
    <AppInput v-model="form.email" label="E-mail" type="email" required />
    <AppInput
      v-if="!modelValue"
      v-model="form.password"
      label="Senha"
      type="password"
      required
    />

    <div class="actions">
      <AppButton variant="secondary" @click="$emit('cancel')">Cancelar</AppButton>
      <AppButton type="submit" :loading="loading">Salvar</AppButton>
    </div>
  </form>
</template>
```

---

### 🔹 Pages (magras)

```vue
<!-- src/pages/users/Index.vue -->
<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useUsers } from '@/composables/useUsers'
import { ROUTE_NAMES } from '@/constants'
import UserTable from '@/components/domain/users/UserTable.vue'
import AppButton from '@/components/common/AppButton.vue'

const router = useRouter()
const { users, loading, isEmpty, fetchUsers, deleteUser } = useUsers()

onMounted(() => fetchUsers())
</script>

<template>
  <div>
    <header class="flex justify-between mb-4">
      <h1>Usuários</h1>
      <AppButton @click="router.push({ name: ROUTE_NAMES.USERS.CREATE })">
        Novo Usuário
      </AppButton>
    </header>

    <UserTable
      :users="users"
      :loading="loading"
      :empty="isEmpty"
      @delete="deleteUser"
      @edit="(id) => router.push({ name: ROUTE_NAMES.USERS.EDIT, params: { id } })"
      @show="(id) => router.push({ name: ROUTE_NAMES.USERS.SHOW, params: { id } })"
    />
  </div>
</template>
```

```vue
<!-- src/pages/users/Create.vue -->
<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useUsers } from '@/composables/useUsers'
import { ROUTE_NAMES } from '@/constants'
import UserForm from '@/components/domain/users/UserForm.vue'

const router = useRouter()
const { loading, createUser } = useUsers()
</script>

<template>
  <div>
    <h1>Novo Usuário</h1>
    <UserForm
      :loading="loading"
      @submit="createUser"
      @cancel="router.push({ name: ROUTE_NAMES.USERS.INDEX })"
    />
  </div>
</template>
```

```vue
<!-- src/pages/users/Edit.vue -->
<script setup lang="ts">
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useUsers } from '@/composables/useUsers'
import { ROUTE_NAMES } from '@/constants'
import UserForm from '@/components/domain/users/UserForm.vue'

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
      @cancel="router.push({ name: ROUTE_NAMES.USERS.INDEX })"
    />
  </div>
</template>
```

```vue
<!-- src/pages/users/Show.vue -->
<script setup lang="ts">
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useUsers } from '@/composables/useUsers'
import { ROUTE_NAMES } from '@/constants'
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
    <p>Criado em: {{ formatDate(user.created_at) }}</p>

    <AppButton
      @click="router.push({ name: ROUTE_NAMES.USERS.EDIT, params: { id: user.id } })"
    >
      Editar
    </AppButton>
  </div>
</template>
```

---

### 🔹 Router

```ts
// src/router/index.ts
import { createRouter, createWebHistory } from 'vue-router'
import { ROUTES, ROUTE_NAMES } from '@/constants'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: ROUTES.USERS.INDEX,
      name: ROUTE_NAMES.USERS.INDEX,
      component: () => import('@/pages/users/Index.vue'),
    },
    {
      path: ROUTES.USERS.CREATE,
      name: ROUTE_NAMES.USERS.CREATE,
      component: () => import('@/pages/users/Create.vue'),
    },
    {
      path: '/users/:id/edit',
      name: ROUTE_NAMES.USERS.EDIT,
      component: () => import('@/pages/users/Edit.vue'),
    },
    {
      path: '/users/:id',
      name: ROUTE_NAMES.USERS.SHOW,
      component: () => import('@/pages/users/Show.vue'),
    },
  ],
})

export default router
```

---

## 🚫 Regras Proibidas (Anti-patterns)

| ❌ Anti-pattern                                        | ✅ Correto                                          |
| ------------------------------------------------------ | -------------------------------------------------- |
| `axios.get()` dentro de `.vue`                         | Chamar via composable → service                    |
| `ref()`/`reactive()` dentro de Pages                   | Mover para composable                              |
| String `'/users'` hardcoded                            | Usar `ROUTES.USERS.INDEX`                          |
| Mensagem `'Sucesso!'` hardcoded                        | Usar `MESSAGES.USER.CREATED`                       |
| Componente que faz HTTP                                | Componente emite evento; composable chama service  |
| Lógica de formatação duplicada                         | Extrair para `utils/format.ts`                     |
| Validar e-mail com regex solta em vários lugares       | Usar `utils/validators.isEmail`                    |
| Página com 300+ linhas                                 | Quebrar em componentes + composable                |
| Composable renderizando HTML                           | Composable só lógica; componente renderiza         |
| Estado local de página no Pinia                        | Usar composable; Pinia só para estado global       |

---

## ✅ Checklist de Pull Request

### Pages
- [ ] Existe `Index.vue`, `Create.vue`, `Edit.vue`, `Show.vue`
- [ ] Página não contém `ref`/`reactive`/`computed`
- [ ] Página não faz chamada HTTP direta
- [ ] Página < 80 linhas

### Components
- [ ] Recebe `props` tipadas
- [ ] Emite `events` em vez de navegar
- [ ] Reutilizável (ou responsabilidade única)
- [ ] Sem chamadas HTTP

### Composables
- [ ] Prefixo `use`
- [ ] Contém toda a lógica da página
- [ ] Chama `Service`, nunca `axios` direto
- [ ] Retorna refs/computed/methods

### Services
- [ ] Usa `http.ts` (axios instance)
- [ ] Retorna `Promise<T>` tipado
- [ ] Sem regra de negócio

### Constants
- [ ] Nenhuma string mágica no código
- [ ] Rotas centralizadas em `routes.ts`
- [ ] Mensagens em `messages.ts`
- [ ] Enums em `status.ts`

### Utils
- [ ] Funções puras
- [ ] Tipadas
- [ ] Com teste unitário (quando crítico)

---

## 🧪 Testes (recomendado)

| Camada      | Ferramenta            | Foco                              |
| ----------- | --------------------- | --------------------------------- |
| Utils       | Vitest                | Entrada/saída                     |
| Composables | Vitest + `@vue/test-utils` | Estado e chamadas ao service |
| Components  | Vitest + `@vue/test-utils` | Props, emits, renderização   |
| Pages       | Cypress / Playwright  | E2E de fluxo                      |

### Exemplo — Teste de Util

```ts
// tests/utils/format.spec.ts
import { describe, it, expect } from 'vitest'
import { formatCurrency, formatCpf } from '@/utils'

describe('formatCurrency', () => {
  it('formats BRL', () => {
    expect(formatCurrency(1234.5)).toBe('R$ 1.234,50')
  })
})

describe('formatCpf', () => {
  it('formats CPF', () => {
    expect(formatCpf('12345678901')).toBe('123.456.789-01')
  })
})
```

### Exemplo — Teste de Composable

```ts
// tests/composables/useUsers.spec.ts
import { describe, it, expect, vi } from 'vitest'
import { useUsers } from '@/composables/useUsers'
import { userService } from '@/services/userService'

vi.mock('@/services/userService')

describe('useUsers', () => {
  it('fetches users', async () => {
    vi.mocked(userService.list).mockResolvedValue({
      data: [{ id: 1, name: 'John', email: 'j@j.com' }],
      meta: { current_page: 1, last_page: 1, per_page: 15, total: 1 },
    } as any)

    const { users, fetchUsers } = useUsers()
    await fetchUsers()

    expect(users.value).toHaveLength(1)
  })
})
```

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