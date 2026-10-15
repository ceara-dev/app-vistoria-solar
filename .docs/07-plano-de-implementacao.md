# 07. Plano de implementação

As fases seguem uma ordem de dependência. Cada uma termina com testes passando e pode virar um PR próprio. Os itens marcados com ⚠️ precisam de aprovação antes, por adicionarem dependência ou de uma das [decisões em aberto](README.md#decisões-em-aberto).

## Fase 0: preparação ✅ (15/10/2026)

- [x] Adicionar `/storage/time-machine` ao `.gitignore` e rodar `git rm -r --cached storage/time-machine`.
- [x] Garantir `TIME_MACHINE_ENABLED=false` no `.env.example`, valor herdado por todo ambiente que não seja local.
- [x] Criar `tests/Pest.php` ([doc 06, seção 3](06-seguranca-e-testes.md#3-preparação-dos-testes)).
- [x] Recuperar os testes de autenticação do Breeze, versão Pest (`vendor/laravel/breeze/stubs/default/pest-tests/Feature/Auth`), e ajustá-los às telas atuais.

**Pronto quando:** `php artisan test --compact` passa com os testes de autenticação.

## Fase 1: núcleo da tenancy ✅ (15/10/2026)

Só backend e infraestrutura. Nenhuma rota passa a exigir empresa ainda, porque o cadastro atual cria usuários sem empresa. Isso muda na fase 2.

- [x] Migrations: `tenants`, `tenant_user`, e `current_tenant_id` e `is_central_admin` em `users` ([doc 03, seção 2](03-modelo-de-dados.md#2-tabelas-novas-da-tenancy)).
- [x] `Tenant` com factory e estados, enum `TenantStatus`, e relações em `User` ([doc 04, seção 6](04-implementacao-backend.md#6-models-tenant-e-user)).
- [x] `app/Tenancy`: `TenantContext`, `TenantScope`, `BelongsToTenant`, `TenantRule`, `TenantStatus`, `CurrentTenantResolver` e `TenantNotResolvedException`.
- [x] `TenantContext` como `scoped` e o callback `Context::hydrated` no `AppServiceProvider`.
- [x] Middleware `SetCurrentTenant`, com o alias `tenant` e a prioridade antes de `SubstituteBindings`.
- [x] Testes do núcleo e o teste de arquitetura ([doc 06, seção 4](06-seguranca-e-testes.md#núcleo-fase-1)).

**Pronto quando:** um model de teste com `BelongsToTenant` passa por todos os testes de núcleo, inclusive o de filas.

O model de teste é `tests/Fixtures/TenantNote.php`. Os testes do middleware registram rotas provisórias para `tenants.select` e `tenants.blocked`, que só ganham telas na fase 2.

## Fase 2: front em TypeScript, cadastro, membros e troca de empresa ✅ (15/10/2026)

- [x] Front em TypeScript na estrutura do [doc 08, seção 3](08-padroes-de-arquitetura.md#3-frontend): `tsconfig.json`, `app.ts`, `Types/global.d.ts` com o `route()` do Ziggy e as props compartilhadas, componentes comuns com prefixo `App`, `npm run typecheck`.
- [x] Vitest (`npm run test:unit`), com testes de `Utils/validators` e `useFormValidation` em `tests/js`.
- [x] `APP_LOCALE=pt_BR`, `lang/pt_BR` com os textos do `VALIDATION.md` e validação espelhada no front (`useFormValidation`, contador de caracteres no `AppTextInput`).
- [x] Rotas de empresa em `/app/*` (`tenant.*`) com `['auth', 'verified', 'tenant']`. `/dashboard` virou `tenant.dashboard`.
- [x] Cadastro público: `RegisterRequest` → `RegisteredUserController` → `RegistrationService`, com empresa em teste de 14 dias e papel `proprietario`.
- [x] Escolha de empresa (com opção de criar uma nova), troca pelo topo da tela e tela de empresa bloqueada.
- [x] `HandleInertiaRequests` com `auth.user` explícito e `tenant`, `tenants` e `can` como closures.
- [x] `TenantLayout.vue` com o seletor de empresa. O painel foi para `Pages/Tenant/Dashboard/Index.vue`.
- [x] Convites: `tenant_invitations`, e-mail com o link, aceite por quem já tem conta ou criando a conta no aceite.
- [x] Tela de membros: listar, mudar papel e remover acesso. Ninguém altera o próprio acesso, e só proprietário mexe em proprietário.
- [x] **Puxado da fase 3:** spatie com `teams` (`tenant_id`), `RolesAndPermissionsSeeder` com os quatro papéis e as permissões `members.view` e `members.manage`. O cadastro e os convites precisavam atribuir papel.
- [ ] **Movido para a fase 4:** as regras `Cpf`, `Cnpj` e `PhoneBr`. Nenhum formulário da fase 2 tem esses campos, e criá-las agora seria código sem uso.

**Pronto quando:** duas pessoas criam empresas, uma convida a outra, e a convidada alterna entre as duas sem sair da conta.

Verificado com 106 testes de backend, 20 de front e um roteiro no Chromium headless: cadastro, validação no front, convite, aceite pelo celular e permissões do gestor.

**Deploy:** `php artisan db:seed --class=RolesAndPermissionsSeeder --force` precisa rodar a cada mudança em `TenantRole` ou `TenantPermission`.

## Fase 3: permissões e auditoria ✅ (15/10/2026)

- [x] spatie com `teams` (`tenant_id`), seeder dos papéis globais e `HasRoles` em `User`. *(feito na fase 2)*
- [x] Molde de policy por módulo: `TenantInvitationPolicy` confere a permissão e se o registro é da empresa atual. As rotas e o FormRequest de convites passaram a usá-la ([doc 04, seção 7](04-implementacao-backend.md#como-autorizar)).
- [x] Matriz completa papel × permissão testada no Gate, mais o teste de que o papel de uma empresa não vale em outra.
- [x] laravel-auditing publicado, com `audits.tenant_id`, `TenantIdResolver`, `App\Models\Audit` com escopo, `Tenant` e `TenantInvitation` auditáveis (sem o hash do token).
- [x] Vínculos e papéis no histórico com `TenantAuditTrail` (`member_activated`, `member_deactivated`, `member_role_changed`), registrando quem fez e os valores antes e depois.
- [x] `tenant_id` passa a ser inteiro em todo model com `BelongsToTenant` (`initializeBelongsToTenant`).

**Pronto quando:** os testes de papel cruzado entre empresas e de `audits.tenant_id` passam.

Verificado com 130 testes. Também desliguei de propósito três proteções, e os testes certos falharam em cada caso: a checagem de empresa na policy, a exclusão do token no histórico e o escopo do `Audit`. O rollback das migrations novas foi testado no MySQL.

**Ficou para depois:**

- **Tela de histórico da empresa:** entra quando um módulo precisar dela.
- **Policies de cada módulo:** nascem com o próprio módulo, a partir da fase 4.
- **Guard `sanctum` em `audit.user.guards`:** entra na fase 5.

## Fase 4: primeiro módulo de domínio (piloto) ✅ (15/10/2026)

O módulo **Clientes** validou o molde de ponta a ponta.

- [x] A decisão do catálogo de equipamentos não foi necessária: o piloto não inclui sistemas. Ela continua em aberto para o módulo de sistemas.
- [x] Regras `Cpf`, `Cnpj`, `PhoneBr` e `ZipCode` em `app/Rules`, com mensagens em `lang/pt_BR/validation.php`, validadores equivalentes em `Utils/validators.ts` e os mesmos casos de teste nos dois lados. `INVALID_PHONE` e `INVALID_ZIP_CODE` foram acrescentados ao `VALIDATION.md`.
- [x] `customers`, `customer_addresses` e `customer_contacts`, com `tenant_id`, índices compostos, chave estrangeira composta `(tenant_id, customer_id)` e auditoria.
- [x] Camadas do [doc 08](08-padroes-de-arquitetura.md#2-backend):
  - `IndexCustomerRequest`, `StoreCustomerRequest` e `UpdateCustomerRequest`, com `TenantRule`;
  - `Tenant\CustomerController`, com 7 métodos;
  - `CustomerService`, `CustomerRepository` com contrato e `CustomerResource`;
  - `CustomerPolicy`;
  - permissões `customers.view` (todos os papéis) e `customers.manage` (todos menos vistoriador).
- [x] Front: `Pages/Tenant/Customers/{Index,Create,Edit,Show}.vue`, `useCustomerForm`, `useCustomerSearch`, `Components/tenant/customers/`, `AppTextarea` e `AppPagination`.
- [x] *Dataset* de isolamento em `tests/Datasets/TenantIsolation.php`: as quatro rotas de registro com cliente de outra empresa dão 404 e o registro não muda.
- [x] Revisão do molde (abaixo).

**Pronto quando:** o CRUD de clientes passa no dataset de isolamento. Os módulos seguintes (sistemas, vistorias, processos, estoque, agenda) seguem o mesmo molde.

**Como verifiquei:**

- 185 testes de backend e 48 de front passam.
- Desliguei de propósito o filtro de empresa no CPF/CNPJ único e a restrição de endereço ao próprio cliente. Os testes certos falharam nos dois casos.
- Rodei o fluxo no Chromium headless: lista, validação no front, cadastro, edição, busca, exclusão, e outra empresa recebendo lista vazia e 404 no cliente alheio.

### Revisão do molde: o que o piloto mudou

| Atrito | Ajuste |
|---|---|
| As policies repetiam "o registro é da empresa atual?" | `TenantContext::owns(Model)`, usado por `CustomerPolicy` e `TenantInvitationPolicy` |
| Listas aninhadas (endereços, contatos) não cabiam na validação do front | `useFormValidation` aceita `addresses.*.street`, como no Laravel. No backend, `TenantRule::exists('customer_addresses')->where('customer_id', ...)` garante que o id é do próprio cliente, sem mudar o `TenantRule` |
| Factory de tabela filha não sabe a empresa | A factory deriva o `tenant_id` do pai com `withoutGlobalScope(TenantScope::class)`, declarando o pai antes da empresa. Exceção aceita só em factories |
| Relação reaproveitada acumula filtros (`$customer->addresses()->findOrFail()` muda a query) | O repositório recria a relação a cada consulta |
| `DatabaseSeeder` roda sem eventos de model | Dados de empresa no seeder sempre com `for($tenant)`. Esses dados também não geram auditoria |
| `cpf()` e `cnpj()` do Faker só existem em `pt_BR` | `APP_FAKER_LOCALE=pt_BR` fixado no `phpunit.xml` |

**Em aberto:** o índice único `(tenant_id, document)` inclui clientes excluídos. Recadastrar o CPF de um cliente que foi para a lixeira dá "Este valor já está em uso". Falta decidir se a tela deve oferecer restaurar o cliente ou se a exclusão deve liberar o documento.

## Fase 5: API e app ✅ (15/10/2026)

- [x] Sanctum 4.3 com `php artisan install:api`, aprovado em 15/10/2026.
- [x] `personal_access_tokens.tenant_id`, model `PersonalAccessToken` próprio, middleware `tenant.token` (`SetTenantFromToken`) e `sanctum` em `audit.user.guards`.
- [x] Login do app com escolha de empresa, `GET /me` e logout ([doc 05, seção 2](05-api-e-app-mobile.md#2-token-atrelado-à-empresa)).
- [x] `trips` e `trip_points` com UUID do aparelho, `PUT /trips/{uuid}` idempotente e pontos em lote, também idempotentes. A permissão `trips.record` foi dada a todos os papéis.
- [x] No app:
  - `http`, `flutter_secure_storage` e `uuid` (aprovados);
  - banco local na versão 3, com um arquivo por empresa e usuário;
  - login com escolha de empresa;
  - sincronização com nova tentativa e espera crescente;
  - importação das rotas gravadas antes do login.
- [x] Testes de API ([doc 06, seção 4](06-seguranca-e-testes.md#api-fase-5)).

**Pronto quando:** uma rota gravada offline sincroniza para a empresa certa, e o reenvio não duplica nada.

**Como verifiquei:**

- **SAAS:** 216 testes de backend. Desliguei de propósito a prioridade do `tenant.token` e a checagem de dono da rota, e os testes certos falharam.
- **App:** 36 testes (cliente da API com `MockClient`, sessão, sincronizador, tela de login) e `flutter analyze` sem avisos.
- **Contrato de ponta a ponta:** o `ClienteApi` do app rodou contra o servidor real num SQLite isolado:
  - senha errada vira o erro certo;
  - o login traz empresa e permissões;
  - o PUT da rota funciona duas vezes;
  - 501 pontos entram em dois lotes;
  - o reenvio grava 0;
  - o token revogado vira `SessaoEncerrada`.

**Não verificado:** o app rodando num aparelho ou emulador (GPS, armazenamento seguro, tela de login no Android ou iOS). Os testes cobrem a lógica e a tela de login no ambiente de widget.

## Fase 6: Central ✅ (15/10/2026)

- [x] ***Impersonate*:** a Central sai sem ele; a equipe usa só o painel. Ver o sistema como a empresa vê fica para quando houver pedido, com auditoria ([decisão 16](README.md#decisões-tomadas)).
- [x] **Limites por plano:** existem no plano (JSON) e a Central os cadastra, mas ainda não fiscalizam nada; entram em vigor com a cobrança ([decisão 17](README.md#decisões-tomadas)).
- [x] Rotas `/central/*` (`central.*`) com o middleware `central.admin` (`EnsureCentralAdmin`), fora do grupo `tenant`. `CentralLayout.vue` e `CentralNavLinks.vue` com navegação própria.
- [x] Empresas: listar com busca, filtro de situação e contadores (membros ativos e clientes), criar com `admin_email` (convite de `proprietario`), suspender, reativar e estender o teste (mais 14 dias, contando do fim atual ou de agora).
- [x] Planos: tabela `plans` com nome único, preço e limites; `tenants.plan_id`; telas de listar, criar e editar. Sem exclusão: um plano sai de linha com `is_active`.
- [x] Clientes B2B: tabela `b2b_clients` (razão social, nome fantasia, CNPJ e contato) com telas de listar, criar, editar e excluir. A empresa nova nasce vinculada a um cliente (`tenants.b2b_client_id`, `nullOnDelete`: excluir o cliente só desvincula).
- [x] `central.*` fora do Ziggy nas páginas das empresas: `config/ziggy.php` com `except` e o grupo `central`, que `app.blade.php` publica nas telas da Central.
- [x] Repositórios da Central em `Repositories/Central/`: `TenantRepository` (`customersCountByTenant` atravessa empresas com `withoutGlobalScope` uma vez só, com teste próprio) e `PlanRepository`.
- [x] O convite de proprietário da empresa criada pela Central (`InvitationService::inviteOwner`) roda no contexto da empresa nova (`TenantContext::run`), porque a linha de `tenant_invitations` é dela.

**Pronto quando:** a equipe da Central cria uma empresa, o convite chega ao administrador e as ações de suspender, reativar e estender o teste funcionam — sem que um membro de empresa acesse `/central`.

**Como verifiquei:**

- 281 testes de backend e 59 de front passam.
- Os testes que importam estão explícitos: membro com todas as permissões leva 403 em `/central`; a página da empresa não publica `central.*` no Ziggy; a contagem de clientes por empresa (Central) não vaza entre elas e o rollback das migrations novas rodou no MySQL.
- `DatabaseSeeder` completo num SQLite descartável: papéis, planos, cliente B2B e dados de exemplo.
- Roteiro no Chromium headless: login, painel, lista de empresas, cadastro com convite, suspender, reativar, criar e editar plano, cadastrar cliente B2B, criar empresa vinculada, editar, excluir com desvínculo — sem erro de console.

**Deploy:** `db:seed --class=RolesAndPermissionsSeeder --force` (como antes) e agora também `--class=PlansSeeder --force`, para os planos de exemplo.

## Fase 7: operação

- [ ] Arquivos em `tenants/{id}/` com download por controller ([doc 04, seção 10](04-implementacao-backend.md#10-arquivos)).
- [ ] `TenantContext::cacheKey()` em todo cache de dado de empresa.
- [ ] Comandos agendados percorrendo as empresas ativas.
- [ ] `tenants:export {tenant}` para atender pedidos de portabilidade (LGPD).
- [ ] `tenants:purge {tenant}` para apagar as tabelas filhas, depois as pais e por fim os arquivos, de empresas canceladas há mais tempo que o prazo de retenção. Incluir `audits` e as linhas de `roles`, `model_has_roles` e `model_has_permissions` da empresa, que não têm chave estrangeira para `tenants`.
- [ ] `tenant_id` aparecendo nos logs (vem do `Context`) e nos alertas de erro.
