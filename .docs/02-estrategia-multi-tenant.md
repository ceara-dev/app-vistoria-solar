# 02. Estratégia de multi-tenant

**Tenant** é a empresa cliente do SaaS: uma integradora solar ou uma empresa de vistoria. Na interface ela aparece como **Empresa**. No código, o model é `Tenant` e a coluna é `tenant_id`.

## 1. Modelo de isolamento

| Critério | A. Banco único com `tenant_id` | B. Um banco por empresa | C. Um schema por empresa |
|---|---|---|---|
| Disponível no MySQL | Sim | Sim | Não (é recurso do PostgreSQL) |
| Isolamento | Lógico, depende do código filtrar certo | Físico | Físico |
| Criar uma empresa | Um `INSERT` | Criar banco, usuário e rodar as migrations | Criar schema e rodar as migrations |
| Rodar migrations | Uma vez | Uma vez por empresa (N bancos) | Uma vez por empresa |
| Backup e restauração | Um backup. Restaurar uma empresa só exige script | Por empresa, simples | Por empresa |
| Painel da plataforma (totais, suporte) | Consulta direta | Precisa varrer N bancos | Precisa varrer N schemas |
| Fila, cache e sessão | Compartilhados | Precisa trocar a conexão em cada job | Idem |
| Apagar uma empresa (LGPD) | `DELETE` por `tenant_id`, com job de expurgo | `DROP DATABASE` | `DROP SCHEMA` |
| Risco principal | Uma consulta sem filtro mostra dados de outra empresa | Erro de conexão e custo operacional | Idem B |

### Recomendação: A, banco único com `tenant_id`

- O público é de pequenas e médias empresas: muitas empresas, cada uma com pouco volume. A opção B multiplicaria a operação (migrations, backups, conexões) sem ganho real.
- O painel da plataforma, o suporte e os relatórios gerais ficam simples.
- O risco da opção A, uma consulta sem filtro, é tratado em quatro camadas, descritas no [doc 04](04-implementacao-backend.md) e no [doc 06](06-seguranca-e-testes.md):
  1. Escopo global que **falha fechado**: sem empresa definida, a consulta lança exceção em vez de trazer tudo.
  2. Chave estrangeira composta `(tenant_id, id)` nas relações principais. O banco recusa vincular, por exemplo, uma vistoria a um cliente de outra empresa.
  3. Regras de validação sempre filtradas pela empresa.
  4. Testes de isolamento automáticos para todo model que tem `tenant_id`.
- A porta para a opção B continua aberta. Se um cliente grande exigir banco dedicado no futuro, os IDs são únicos na instalação inteira, então os dados dele podem ser exportados com `WHERE tenant_id = X` sem colisão.

## 2. Pacote ou código próprio

| Opção | Suporta Laravel 13 | Prós | Contras |
|---|---|---|---|
| stancl/tenancy 3.10.1 | Sim (`^13.0`) | Muito usado, cobre banco por empresa e identificação por domínio | Foi feito pensando no modelo B. Em banco único, quase tudo fica sem uso e ainda assim entra uma camada de "mágica" (bootstrappers, eventos) |
| spatie/laravel-multitenancy 4.2.1 | Sim (`^13.0`) | Leve, baseado em tarefas de troca de contexto | Em banco único, o escopo global teria que ser escrito do mesmo jeito |
| Código próprio | — | Poucas classes, comportamento explícito, sem dependência nova | A equipe mantém o código (testado no [doc 06](06-seguranca-e-testes.md)) |

**Recomendação: código próprio.** O Laravel 13 já entrega todas as peças:

- escopos globais (`#[ScopedBy]` ou `addGlobalScope`);
- `Context`, que é serializado no payload dos jobs e restaurado no worker;
- `scoped()` no container, que zera o estado entre jobs;
- prioridade de middleware (`prependToPriorityList`).

Somando tudo, são cerca de seis classes, descritas no [doc 04](04-implementacao-backend.md).

## 3. Como identificar a empresa

| Canal | Como | Por quê |
|---|---|---|
| Web (Inertia), rotas em `/app/*` | `users.current_tenant_id`, conferido a cada requisição contra o vínculo em `tenant_user` | Uma tela de login, um cookie e o `localhost:8000` funcionando sem configurar DNS. Quem trabalha para duas empresas troca sem sair da conta |
| App (API) | Coluna `tenant_id` no token Sanctum (`personal_access_tokens`) | Um token vazado só abre uma empresa. Trocar de empresa no app gera outro token |
| Filas | `tenant_id` no `Context`, restaurado no início do job | Automático para qualquer job, notificação ou listener enfileirado |
| Console e agendador | Explícito: `TenantContext::run($tenant, fn () => ...)` | Comando de manutenção nunca deve "herdar" uma empresa por acaso |

### Por que não subdomínio (`empresa.vistoriasolar.com.br`)

O subdomínio exige DNS curinga, certificado curinga, cookie de sessão por domínio e configuração extra em dev. Como o `TenantContext` é o único lugar que guarda a empresa atual, dá para adicionar a identificação por domínio depois (para *white label*, por exemplo) sem mexer no restante. A coluna `tenants.domain`, que aparece no tipo `Tenant` dos padrões de `.projects`, fica criada e reservada para isso.

**Decidido em 15/10/2026:** identificação pela sessão, com as telas da empresa em `/app/*`.

### Por que não prefixo na URL (`/{empresa}/clientes`)

Toda chamada a `route()`, no PHP e no Ziggy, passaria a exigir o parâmetro da empresa. É muito ruído para um ganho que a sessão já entrega.

## 4. Usuários e pertencimento

**Decidido em 15/10/2026: muitos para muitos.**

```
users ──< tenant_user >── tenants
  └── current_tenant_id ──────┘
```

- O **e-mail é único na instalação inteira**: um login para todas as empresas da pessoa. É isso que permite identificar a empresa depois do login, sem subdomínio.
- `tenant_user` registra o vínculo e se ele está ativo. O **papel** fica nas tabelas do spatie, por empresa (próxima seção).
- Quando a pessoa sai de uma empresa, o vínculo é desativado ou removido. Se essa era a empresa atual, `current_tenant_id` volta a `null` e ela escolhe outra no próximo acesso.

A alternativa descartada era um para muitos, com `users.tenant_id`. Ela obrigaria o e-mail a ser único por empresa, e então o login precisaria saber a empresa antes (subdomínio ou campo extra no formulário).

### Papéis

São papéis globais, definidos pela plataforma e atribuídos por empresa. Usam spatie/laravel-permission com `teams` e `team_id = tenant_id`.

| Papel | Pode |
|---|---|
| `proprietario` | Tudo, inclusive dados da empresa, cobrança e excluir a empresa |
| `administrador` | Tudo, menos cobrança e excluir a empresa |
| `gestor` | Clientes, vistorias, processos, agenda e estoque. Não gerencia membros |
| `vistoriador` | As próprias vistorias e rotas, além de consultar clientes e agenda |

A mesma pessoa pode ser `administrador` em uma empresa e `vistoriador` em outra. Mais adiante, cada empresa pode criar papéis próprios: são linhas em `roles` com o `tenant_id` dela.

### Central (equipe do SaaS)

Os padrões de `.projects` dividem o sistema em dois contextos, e esta documentação usa os mesmos nomes:

| Contexto | Quem acessa | URL | Nomes de rota | Pages Inertia |
|---|---|---|---|---|
| **Central** | Equipe interna do SaaS | `/central/*` | `central.*` | `Pages/Central/...` |
| **Tenant** | Membros de uma empresa | `/app/*` | `tenant.*` | `Pages/Tenant/...` |

- `users.is_central_admin` (boolean) dá acesso à Central, com rotas próprias e sem empresa no contexto.
- A Central gerencia empresas, planos e faturas.
- As consultas da Central removem o escopo de propósito, com `withoutGlobalScope(TenantScope::class)`.
- **Recomendação:** não usar `Gate::before` para liberar tudo dentro das telas das empresas. Se a equipe precisar ver o sistema como a empresa vê, isso deve ser feito por *impersonate* com registro em auditoria (decisão 4 do [README](README.md#decisões-em-aberto)).

## 5. Cadastro e convites

**Decidido em 15/10/2026: os dois caminhos.**

- **Cadastro público:** o formulário ganha o campo "Nome da empresa". Uma transação cria a empresa (`status = trial`), o usuário, o vínculo e o papel `proprietario`, e define essa empresa como a atual.
- **Pela Central:** a equipe cria a empresa informando o e-mail do administrador (`admin_email`, como no `CreateTenantPayload` dos padrões). O sistema envia um convite com o papel `proprietario`, e daí em diante segue o fluxo de convite abaixo.
- **Convite:** o proprietário ou um administrador convida por e-mail, escolhendo o papel. O link é assinado e expira.
  - Se o e-mail já tem conta, o vínculo é criado no aceite.
  - Se não tem, a pessoa cria a senha no aceite e já entra na empresa.
- **Seleção de empresa:** quem tem mais de uma empresa e ainda não tem `current_tenant_id` cai numa tela de escolha. Depois disso, um seletor no topo do layout permite trocar.

## 6. Ciclo de vida da empresa

| Status | Acesso |
|---|---|
| `trial` | Normal até `trial_ends_at`, depois se comporta como `suspended` |
| `active` | Normal |
| `suspended` | Login funciona, mas só mostra uma tela de aviso (inadimplência, bloqueio) |
| `canceled` | Sem acesso. Os dados ficam retidos pelo prazo combinado e depois o job de expurgo os apaga |

Os padrões de `.projects` preveem planos e faturas na Central. A tabela `plans` e a coluna `tenants.plan_id` entram junto com a Central (fase 6). Os limites de cada plano, como usuários ou vistorias por mês, ainda precisam ser definidos.
