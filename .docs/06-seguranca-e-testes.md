# 06. Segurança e testes

No banco único, a falha que importa é **uma empresa ver ou alterar dados de outra**. Este documento lista por onde isso pode acontecer e os testes que fecham cada caminho.

## 1. Caminhos de vazamento

| # | Caminho | Proteção | Onde |
|---|---|---|---|
| 1 | Consulta Eloquent sem filtro | `TenantScope` falha fechado | [04 §3](04-implementacao-backend.md#3-escopo-global-tenantscope) |
| 2 | ID de outra empresa na URL (`/clientes/123`) | O *binding* usa o escopo e responde 404. O middleware `tenant` tem prioridade sobre `SubstituteBindings` | [04 §5](04-implementacao-backend.md#5-middleware-setcurrenttenant) |
| 3 | ID de outra empresa no corpo (`customer_id`) | `TenantRule::exists` e a chave estrangeira composta | [04 §12](04-implementacao-backend.md#12-validação-e-consultas-fora-do-eloquent), [03 §4](03-modelo-de-dados.md#4-convenções-para-tabelas-de-empresa) |
| 4 | `tenant_id` enviado pelo cliente | Fora do `#[Fillable]` e o trait rejeita empresa diferente | [04 §4](04-implementacao-backend.md#4-trait-belongstotenant) |
| 5 | `DB::table`, `join`, SQL cru | Regra de code review e teste de isolamento por relatório | [04 §12](04-implementacao-backend.md#12-validação-e-consultas-fora-do-eloquent) |
| 6 | Job rodando na empresa errada ou sem empresa | `Context` restaurado em todo job, e `TenantContext` como `scoped` | [04 §9](04-implementacao-backend.md#9-filas-console-e-agendador) |
| 7 | Papel de uma empresa valendo em outra | spatie com `teams` e `unsetRelation` na troca | [04 §7](04-implementacao-backend.md#7-papéis-e-permissões-spatie-com-teams) |
| 8 | Arquivo baixado por quem tem o caminho | Disco privado e download por controller com *binding* e policy | [04 §10](04-implementacao-backend.md#10-arquivos) |
| 9 | Chave de cache igual entre empresas | Prefixo `tenant:{id}:` obrigatório | [04 §13](04-implementacao-backend.md#13-cache-e-limites-de-requisição) |
| 10 | Props do Inertia com colunas a mais | Arrays explícitos em vez de models inteiros | [04 §11](04-implementacao-backend.md#11-inertia-e-front) |
| 11 | Histórico (`audits`) de outra empresa | `audits.tenant_id` e escopo no model `Audit` | [04 §8](04-implementacao-backend.md#8-auditoria) |
| 12 | Usuário removido continua entrando | O middleware confere o vínculo ativo a cada requisição, e a API revoga o token | [04 §5](04-implementacao-backend.md#5-middleware-setcurrenttenant), [05 §2](05-api-e-app-mobile.md#2-token-atrelado-à-empresa) |
| 13 | Ferramenta de debug mostrando dados reais | `TIME_MACHINE_ENABLED=false` fora de local e `storage/time-machine` fora do git | [01 §3](01-diagnostico-atual.md#3-problemas-encontrados) |
| 14 | Canais de *broadcast* (quando existirem) | Canais privados `tenant.{id}.*` autorizados pelo vínculo | Futuro |
| 15 | Busca full-text ou Scout (quando existir) | Filtro por `tenant_id` no índice | Futuro |

## 2. Itens de code review

Estes itens entram no checklist de PR, para qualquer tabela ou consulta de empresa:

- [ ] A migration tem `tenant_id` com `restrictOnDelete`, e os índices e restrições únicas começam por ele.
- [ ] O model usa `BelongsToTenant` e não tem `tenant_id` no `#[Fillable]`.
- [ ] As relações principais têm chave estrangeira composta `(tenant_id, pai_id)`.
- [ ] As validações `exists` e `unique` usam `TenantRule`.
- [ ] Nenhum `DB::table`, `withoutGlobalScope(s)` ou `join` sem `tenant_id` fora da Central.
- [ ] As props do Inertia e as respostas da API são arrays ou *Resources* explícitos.
- [ ] Os arquivos ficam em `tenants/{id}/` no disco privado.
- [ ] As chaves de cache usam `TenantContext::cacheKey()`.
- [ ] Há um teste de isolamento para o recurso novo (seção 4).

## 3. Preparação dos testes

Hoje o Pest 5 está instalado, mas não há `tests/Pest.php`, e os dois testes existentes são classes PHPUnit. Para os testes de tenancy, configurar:

```php
// tests/Pest.php
pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');
```

**Factories:**

- `TenantFactory`, com os estados `trial()`, `suspended()` e `canceled()`.
- `UserFactory->forTenant(Tenant $tenant)`: cria o vínculo ativo e define `current_tenant_id` (fase 1). Na fase 3 ganha o parâmetro `string $role = 'gestor'` para atribuir o papel.
- As factories de domínio recebem `'tenant_id' => Tenant::factory()`. O trait aceita `tenant_id` explícito sem contexto (tabela do [doc 04, seção 4](04-implementacao-backend.md#4-trait-belongstotenant)).

**Helper:**

```php
/**
 * Cria um usuário com papel na empresa e autentica com ele.
 */
function actingAsMemberOf(Tenant $tenant, string $role = 'gestor'): User
{
    $user = User::factory()->forTenant($tenant, $role)->create();
    test()->actingAs($user);

    return $user;
}
```

Os testes rodam em SQLite em memória (`phpunit.xml`). O SQLite aceita chaves estrangeiras compostas e o Laravel liga `foreign_keys` por padrão nessa conexão.

## 4. Testes que precisam existir

### Núcleo (fase 1)

| Teste | Garante |
|---|---|
| Consulta a um model de empresa sem contexto lança `TenantNotResolvedException` | Falha fechada |
| `TenantContext::run` aplica e depois restaura a empresa anterior, inclusive quando o callback lança exceção | Sem vazamento de contexto |
| Criar um model no contexto da empresa A grava `tenant_id = A` | Preenchimento automático |
| Criar um model com `tenant_id = B` no contexto A lança exceção | Trait |
| Alterar `tenant_id` num update lança exceção | Trait |
| `Context` desidratado com A e hidratado num container limpo deixa o `TenantContext` em A | Filas |
| Hidratar sem `tenant_id` zera `TenantContext` e o *team id* do spatie | Worker não herda a empresa do job anterior |
| **Arquitetura:** todo model em `app/Models` cuja tabela tem `tenant_id` usa `BelongsToTenant` (ou `TenantScope`, no caso de `Audit`) | Ninguém esquece o trait |

O teste de arquitetura percorre os models com `Schema::hasColumn($model->getTable(), 'tenant_id')`. Ele vale para todos os módulos futuros sem precisar ser alterado.

### Web (fases 1 e 2)

| Teste | Garante |
|---|---|
| Usuário sem empresa ativa é redirecionado para `tenants.select` | Middleware |
| Usuário com uma única empresa entra direto e `current_tenant_id` é gravado | Middleware |
| Empresa `suspended` ou com teste vencido é redirecionada para `tenants.blocked` | Ciclo de vida |
| Trocar para uma empresa da qual a pessoa não é membro volta com erro de validação em `tenant_id` | Troca de empresa |
| Vínculo desativado no meio da sessão faz a próxima requisição sair da empresa | Remoção de membro |
| O cadastro cria empresa, usuário, vínculo e papel `proprietario` numa transação | Onboarding |
| As props `tenant`, `tenants` e `can` vêm da empresa atual, e `auth.user` só tem `id`, `name` e `email` | Inertia |
| Papel `administrador` em A e `vistoriador` em B: pode gerenciar membros em A, mas não em B | spatie com teams |
| Ação feita em A gera `audits.tenant_id = A` | Auditoria |

### Isolamento por recurso (cada módulo novo, a partir da fase 4)

Um *dataset* do Pest em `tests/Datasets/TenantIsolation.php`, que cada módulo estende com as próprias rotas (o primeiro é `customer record routes`):

| Teste | Esperado |
|---|---|
| A listagem da empresa A não contém registros da empresa B | Escopo |
| `GET`, `PUT` e `DELETE` no ID de B, autenticado em A | 404, e o registro de B continua igual |
| `POST` com `customer_id` de B, autenticado em A | 422 (validação) |
| Download de anexo de B, autenticado em A | 404 |

### API (fase 5)

| Teste | Esperado |
|---|---|
| Login com duas empresas e sem `tenant_id` | 409 com a lista |
| Token da empresa A em recurso de B | 404 |
| Token de um usuário removido da empresa | 401, e o token é revogado |
| Reenvio do mesmo lote de pontos | Nenhuma duplicata |
| `tenant_id` ou `user_id` no corpo da sincronização | Ignorados: valem os do token |
| Empresa suspensa | 423, e o token continua válido para quando o acesso voltar |
| Rota de outra pessoa da mesma empresa | 403 |
| UUID de rota que existe em outra empresa | 409, e a linha alheia não muda |

Todos implementados na fase 5, em `tests/Feature/Http/Controllers/Api/V1` e `tests/Feature/Http/Middleware/SetTenantFromTokenTest.php`.
