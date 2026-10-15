# 📋 Contrato de Validação — Backend ↔ Frontend

> Documento normativo complementar ao `docs/ARCHITECTURE.md` e `docs/FRONTEND.md`.
> **Obrigatório** para todos os desenvolvedores (backend e frontend).

---

## 📑 Índice

1. [Objetivo](#-objetivo)
2. [Princípio Fundamental: Espelhamento](#-princípio-fundamental-espelhamento)
3. [Camadas de Validação](#-camadas-de-validação)
4. [Regras Obrigatórias por Tipo de Campo](#-regras-obrigatórias-por-tipo-de-campo)
5. [Contrato de Mensagens](#-contrato-de-mensagens)
6. [Validação no Backend (Laravel)](#-validação-no-backend-laravel)
7. [Validação no Frontend (Vue)](#-validação-no-frontend-vue)
8. [Fluxo Completo de uma Validação](#-fluxo-completo-de-uma-validação)
9. [Sincronização entre Ambos os Lados](#-sincronização-entre-ambos-os-lados)
10. [Regras Proibidas](#-regras-proibidas)
11. [Checklist de Pull Request](#-checklist-de-pull-request)

---

## 🎯 Objetivo

Definir um **contrato único de validação** que deve ser respeitado simultaneamente por:

- **Backend** (Laravel `FormRequest`)
- **Frontend** (Vue com `AppInput`, `AppDatePicker`, `AppTimePicker`, etc.)

Isso garante que:

- ✅ O usuário recebe feedback **imediato** no frontend
- ✅ O servidor **nunca aceita** dados inválidos
- ✅ As mensagens de erro são **idênticas** em ambos os lados
- ✅ As regras de negócio (ex: idade mínima, horário comercial) são **coerentes**

---

## 🔁 Princípio Fundamental: Espelhamento

> **Toda regra de validação do backend DEVE ter uma regra equivalente no frontend.**
> **Toda regra do frontend DEVE ter uma regra equivalente no backend.**

### Por que espelhar?

| Cenário                          | Backend | Frontend | Resultado                                        |
| -------------------------------- | ------- | -------- | ------------------------------------------------ |
| Só backend valida                | ✅      | ❌       | Usuário descobre erro só após submit (ruim)      |
| Só frontend valida               | ❌      | ✅       | API pode ser chamada direto, dados inválidos entram |
| **Ambos validam (espelhado)**    | ✅      | ✅       | **UX boa + segurança garantida** ✅              |

### Regra de ouro

> **Se você adicionou uma regra no `StoreUserRequest` do Laravel, você DEVE adicionar a mesma regra no `UserForm.vue` do frontend — e vice-versa.**

---

## 🧩 Camadas de Validação

A validação acontece em **três camadas**:

```
┌──────────────────────────────────────────────────────────┐
│  1. FRONTEND (UX)                                        │
│     → Feedback imediato no blur/change                   │
│     → Contador de caracteres sempre visível              │
│     → Bloqueia submit se houver erro                     │
└──────────────────────────────────────────────────────────┘
                        ↓
┌──────────────────────────────────────────────────────────┐
│  2. BACKEND (FormRequest)                                │
│     → Segunda barreira                                   │
│     → Retorna 422 com estrutura padronizada              │
│     → Garante integridade dos dados                      │
└──────────────────────────────────────────────────────────┘
                        ↓
┌──────────────────────────────────────────────────────────┐
│  3. SERVICE (Regra de Negócio)                           │
│     → Validações complexas (ex: saldo, permissão)        │
│     → Lança exceções de domínio                          │
└──────────────────────────────────────────────────────────┘
```

### Responsabilidades por camada

| Camada         | Valida                        | Não valida                    |
| -------------- | ----------------------------- | ----------------------------- |
| **Frontend**   | Formato, tamanho, obrigatório | Regras de negócio complexas   |
| **FormRequest**| Formato, tamanho, unique, exists | Regras de negócio complexas |
| **Service**    | Regra de negócio (saldo, permissão, etc.) | Formato básico      |

---

## 📏 Regras Obrigatórias por Tipo de Campo

Estas regras valem para **todos os campos do sistema**, em **ambos os lados**.

### 🔤 Campos de String

| Regra                      | Backend                        | Frontend                              |
| -------------------------- | ------------------------------ | ------------------------------------- |
| **Obrigatório (se aplicável)** | `required`                  | Atributo `required` + validação       |
| **Tamanho mínimo**         | `min:n`                        | `minlength=n` + validação             |
| **Tamanho máximo**         | `max:n`                        | `maxlength=n` + **contador visível**  |
| **Contador de caracteres** | ❌ (não se aplica)             | ✅ **OBRIGATÓRIO** se houver `max`    |
| **Trim automático**        | `TrimStrings` middleware       | `.trim()` no envio                    |
| **E-mail**                 | `email`                        | `isEmail()` + tipo `email`            |

### 📅 Campos de Data

| Regra                | Backend                              | Frontend                            |
| -------------------- | ------------------------------------ | ----------------------------------- |
| **Formato válido**   | `date` (YYYY-MM-DD)                  | `type="date"` + `isValidDate()`     |
| **Data mínima**      | `after_or_equal:min`                 | Atributo `min` + `isDateAfter()`    |
| **Data máxima**      | `before_or_equal:max`                | Atributo `max` + `isDateBefore()`   |
| **Não pode ser futura** | `before_or_equal:today`           | `isNotFutureDate()`                 |
| **Não pode ser passada** | `after_or_equal:today`            | `isNotPastDate()`                   |
| **Idade mínima**     | `date_format` + regra custom        | `isAdult()`                         |

### 🕐 Campos de Hora

| Regra                | Backend                              | Frontend                            |
| -------------------- | ------------------------------------ | ----------------------------------- |
| **Formato válido**   | `date_format:H:i`                    | `type="time"` + `isValidTime()`     |
| **Hora mínima**      | `after_or_equal:min`                 | Atributo `min` + `isTimeAfter()`    |
| **Hora máxima**      | `before_or_equal:max`                | Atributo `max` + `isTimeBefore()`   |
| **Horário comercial**| Regra custom (`business_hours`)      | `isBusinessHour()`                  |

### 🗓️ Campos de Data + Hora

| Regra                  | Backend                                | Frontend                            |
| ---------------------- | -------------------------------------- | ----------------------------------- |
| **Combinado válido**   | `date_format:Y-m-d H:i`                | `AppDateTimePicker` + `isValidDateTime()` |
| **Não pode ser futuro**| Regra custom                           | `isFutureDateTime()`                |
| **Fuso horário**       | `timezone:America/Sao_Paulo` (se aplicável) | Conversão antes do envio       |

### 🔢 Campos Numéricos

| Regra             | Backend           | Frontend                       |
| ----------------- | ----------------- | ------------------------------ |
| **Tipo**          | `numeric`/`integer` | `type="number"` + validação  |
| **Mínimo**        | `min:n`           | Atributo `min` + validação     |
| **Máximo**        | `max:n`           | Atributo `max` + validação     |

### 📄 Documentos BR

| Documento | Backend              | Frontend           |
| --------- | -------------------- | ------------------ |
| **CPF**   | Regra custom `cpf`   | `isCpf()`          |
| **CNPJ**  | Regra custom `cnpj`  | `isCnpj()`         |
| **Phone** | Regra custom `phone` | `isPhoneBR()`      |

---

## 📨 Contrato de Mensagens

As mensagens de erro **DEVEM SER IDÊNTICAS** em backend e frontend.

### Como funciona

1. **Fonte da verdade:** arquivo `docs/VALIDATION.md` (este documento)
2. Backend define em `FormRequest::messages()`
3. Frontend define em `src/constants/messages.ts` → `MESSAGES.VALIDATION.*`
4. Ambos **DEVEM** usar os mesmos textos

### Mensagens Padronizadas

| Chave                          | Texto final                                                |
| ------------------------------ | ---------------------------------------------------------- |
| `REQUIRED`                     | `Este campo é obrigatório`                                 |
| `MAX_LENGTH`                   | `Máximo de {n} caracteres`                                 |
| `MIN_LENGTH`                   | `Mínimo de {n} caracteres`                                 |
| `INVALID_EMAIL`                | `E-mail inválido`                                          |
| `CONFIRMED`                    | `A confirmação não confere`                                |
| `INVALID_DATE`                 | `Data inválida`                                            |
| `INVALID_TIME`                 | `Horário inválido (use HH:mm)`                             |
| `DATE_BEFORE`                  | `Data deve ser igual ou após {min}`                        |
| `DATE_AFTER`                   | `Data deve ser igual ou antes de {max}`                    |
| `TIME_BEFORE`                  | `Horário deve ser igual ou após {min}`                     |
| `TIME_AFTER`                   | `Horário deve ser igual ou antes de {max}`                 |
| `MIN_AGE`                      | `Idade mínima: {n} anos`                                   |
| `BUSINESS_HOURS`               | `Fora do horário comercial (08:00–18:00)`                  |
| `INVALID_CPF`                  | `CPF inválido`                                             |
| `INVALID_CNPJ`                 | `CNPJ inválido`                                            |
| `INVALID_PHONE`                | `Telefone inválido`                                        |
| `INVALID_ZIP_CODE`             | `CEP inválido`                                             |
| `INVALID_NUMBER`               | `Valor inválido`                                           |
| `MIN_VALUE`                    | `Valor mínimo: {n}`                                        |
| `UNIQUE`                       | `Este valor já está em uso`                                |
| `EXISTS`                       | `O valor selecionado é inválido`                           |

### Regra

> **Toda nova mensagem de validação DEVE ser adicionada primeiro a este documento, depois replicada no backend e no frontend.**

---

## 🔧 Validação no Backend (Laravel)

### Onde fica

- **FormRequest** → `app/Http/Requests/{Domínio}/`
- **Regras customizadas** → `app/Rules/`
- **Mensagens** → dentro do próprio `FormRequest` (`messages()`)

### O que valida

1. Formato básico (`string`, `email`, `date`, `numeric`, etc.)
2. Tamanho (`min`, `max`, `between`)
3. Obrigatoriedade (`required`, `nullable`, `sometimes`)
4. Unicidade (`unique`) e existência (`exists`)
5. Regras customizadas (`Rule::` custom)

### O que NÃO valida no FormRequest

- Regras de negócio complexas (saldo, permissão, hierarquia)
- Essas ficam no **Service** e lançam exceções de domínio

### Estrutura de resposta de erro

Quando a validação falha, o backend retorna:

- **HTTP Status:** `422 Unprocessable Entity`
- **Body:** estrutura com `message` + `errors` (chaveado por campo)

O frontend consome essa resposta e mapeia os erros para os campos do formulário.

### Regras customizadas obrigatórias

O backend **DEVE** ter regras customizadas para:

- `cpf` — validação real de dígitos
- `cnpj` — validação real de dígitos
- `phone_br` — formato brasileiro
- `business_hours` — horário comercial
- `min_age:n` — idade mínima

Estas regras ficam em `app/Rules/` e são reutilizáveis.

---

## 🎨 Validação no Frontend (Vue)

### Onde fica

- **Componentes** → `components/common/AppInput.vue`, `AppDatePicker.vue`, etc.
- **Validadores** → `utils/validators.ts`
- **Composable** → `composables/shared/useFormValidation.ts`
- **Mensagens** → `constants/messages.ts` → `MESSAGES.VALIDATION.*`

### O que valida

1. Formato básico (mesmas regras do backend)
2. Tamanho (com **contador de caracteres obrigatório**)
3. Obrigatoriedade
4. Datas válidas (formato + min/max)
5. Horários válidos (formato `HH:mm` + min/max)
6. Regras customizadas (`isCpf`, `isCnpj`, `isBusinessHour`)

### Comportamento dos Componentes

| Componente         | Comportamento                                                 |
| ------------------ | ------------------------------------------------------------- |
| `AppInput`         | Mostra contador `{atual}/{max}`, bloqueia digitação acima de `maxlength` |
| `AppTextarea`      | Mostra contador, bloqueia acima de `maxlength`                |
| `AppDatePicker`    | Valida formato, min/max, exibe erro abaixo                    |
| `AppTimePicker`    | Valida `HH:mm`, min/max, exibe erro abaixo                    |
| `AppDateTimePicker`| Combina os dois + validação de data futura/passada            |

### Cores do contador

- **< 80%** → cinza
- **≥ 80%** → amarelo (aviso)
- **100%** → vermelho (limite atingido)

### Validação por evento

- **`blur`** → valida o campo individual
- **`submit`** → valida todos os campos antes de enviar
- **`change`** → para selects, datas, horários

### Quando o backend retorna 422

1. O composable intercepta a resposta
2. Mapeia `response.errors` para os campos do formulário
3. Exibe as mensagens nos componentes corretos
4. Rola até o primeiro erro

---

## 🔄 Fluxo Completo de uma Validação

```
┌─────────────────────────────────────────────────────────────┐
│  1. Usuário digita no campo                                  │
│     → Componente atualiza contador de caracteres             │
│     → Bloqueia se ultrapassar maxlength                      │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│  2. Usuário sai do campo (blur)                              │
│     → Composable valida campo                                │
│     → Se erro: exibe mensagem abaixo do campo                │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│  3. Usuário clica em "Salvar"                                │
│     → Composable valida TODOS os campos                      │
│     → Se erro: bloqueia submit e rola até o primeiro erro    │
│     → Se OK: envia via Service (Axios)                       │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│  4. Backend recebe requisição                                │
│     → FormRequest valida                                     │
│     → Se falha: retorna 422 + errors                         │
│     → Se OK: passa para o Controller → Service               │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│  5. Frontend recebe resposta                                 │
│     → 201/200: sucesso → toast + redireciona                 │
│     → 422: mapeia erros para campos → exibe                  │
│     → 500: toast de erro genérico                            │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔗 Sincronização entre Ambos os Lados

### Como garantir que backend e frontend estão sincronizados

1. **Fonte única de verdade:** este documento (`docs/VALIDATION.md`)
2. **Toda regra nova** deve ser adicionada **primeiro** aqui
3. **PRs de backend** que alteram validação **DEVEM** vir acompanhados de PR de frontend (e vice-versa)
4. **Testes** em ambos os lados devem cobrir as mesmas regras
5. **Revisão de PR** deve verificar se as mensagens batem

### Tabela de mapeamento (exemplo)

| Regra            | Backend (`StoreUserRequest`)     | Frontend (`UserForm.vue`)            |
| ---------------- | -------------------------------- | ------------------------------------ |
| Nome obrigatório | `'name' => 'required'`           | `required` + `isRequired()`          |
| Nome máx 100     | `'name' => 'max:100'`            | `:maxlength="100"` + contador        |
| E-mail válido    | `'email' => 'email'`             | `type="email"` + `isEmail()`         |
| E-mail único     | `'email' => 'unique:users,email'`| `unique` (backend only — 422)        |
| Idade mínima 18  | `Rule::minAge(18)`               | `isAdult()`                          |
| Horário comercial| `Rule::businessHours()`          | `isBusinessHour()`                   |

### Atenção às regras **só do backend**

Algumas regras **só podem ser validadas no backend** porque dependem do banco:

- `unique` (e-mail único)
- `exists` (foreign key válida)
- Regras que dependem de outros registros

Nesses casos:

- **Frontend** valida apenas o formato
- **Backend** valida a unicidade
- **Frontend** trata a resposta 422 e exibe o erro

---

## 🚫 Regras Proibidas

| ❌ Anti-pattern                                                  | ✅ Correto                                                       |
| ---------------------------------------------------------------- | --------------------------------------------------------------- |
| Validar só no backend                                            | Espelhar no frontend                                            |
| Validar só no frontend                                           | Espelhar no backend                                             |
| Mensagens diferentes em backend e frontend                       | Usar mensagens deste documento                                  |
| Hardcodar mensagem de validação no `.vue`                        | Usar `MESSAGES.VALIDATION.*`                                    |
| Criar regra custom no backend sem equivalente no frontend        | Criar em ambos                                                   |
| `<input>` sem `maxlength` e sem contador                         | Usar `AppInput` com `maxlength`                                 |
| `<input type="date">` sem validação de min/max                   | Usar `AppDatePicker`                                            |
| `<input type="time">` sem validação de formato                   | Usar `AppTimePicker`                                            |
| Submeter formulário sem chamar `validateAll()`                   | Sempre validar antes do envio                                   |
| Ignorar resposta 422 do backend                                  | Mapear erros para os campos do formulário                       |
| Colocar regra de negócio no `FormRequest`                        | Mover para o `Service`                                          |

---

## ✅ Checklist de Pull Request

### Ao criar/alterar uma validação

- [ ] Regra adicionada a **este documento** (`docs/VALIDATION.md`)
- [ ] Regra implementada no **backend** (FormRequest ou Rule)
- [ ] Regra implementada no **frontend** (validator + componente)
- [ ] **Mensagem idêntica** em ambos os lados
- [ ] Teste de backend cobrindo a regra
- [ ] Teste de frontend cobrindo a regra

### Para campos de String

- [ ] `maxlength` definido no backend (`max:n`)
- [ ] `maxlength` definido no frontend (`:maxlength="n"`)
- [ ] Contador de caracteres visível no frontend
- [ ] Contador muda de cor em 80% e 100%

### Para campos de Data

- [ ] Formato validado no backend (`date`)
- [ ] Formato validado no frontend (`type="date"` + `isValidDate()`)
- [ ] `min`/`max` definidos em ambos os lados (se aplicável)
- [ ] Regras especiais (não futura, não passada, idade mínima) espelhadas

### Para campos de Hora

- [ ] Formato `H:i` validado no backend
- [ ] Formato `HH:mm` validado no frontend
- [ ] `min`/`max` definidos em ambos os lados (se aplicável)
- [ ] Horário comercial validado em ambos (se aplicável)

### Tratamento de erro

- [ ] Frontend mapeia resposta 422 para os campos
- [ ] Mensagens exibidas abaixo do campo correto
- [ ] Scroll automático até o primeiro erro
- [ ] Backend retorna estrutura padronizada (`message` + `errors`)

---

## 📚 Referências

- [Laravel — Validation](https://laravel.com/docs/validation)
- [Laravel — Custom Rules](https://laravel.com/docs/validation#custom-validation-rules)
- [Vue — Form Validation Patterns](https://vuejs.org/guide/essentials/forms.html)
- Documentos internos:
  - [`docs/ARCHITECTURE.md`](./ARCHITECTURE.md) — Backend
  - [`docs/FRONTEND.md`](./FRONTEND.md) — Frontend

---

**Última atualização:** {{ data }}
**Mantido por:** Time de Engenharia (Backend + Frontend)
