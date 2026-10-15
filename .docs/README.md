# Multi-tenant no Vistoria Solar

Análise do projeto e proposta de arquitetura para transformar o SaaS em multi-tenant, ou seja, várias empresas usando a mesma instalação, cada uma vendo apenas os próprios dados.

Escrita em 15/10/2026, sobre o commit `e77ce82` do SAAS, o código atual do app Flutter (`../APP`) e os padrões de arquitetura em `.projects/`.

## Resumo

- **O SAAS ainda é praticamente o starter.** Laravel 13.36 com Breeze (Inertia v3 + Vue 3), só com `users`, autenticação e as páginas Welcome e Dashboard. Não existe nenhuma tabela de domínio. Por isso este é o melhor momento para entrar com multi-tenant: nada precisa ser migrado, e cada módulo novo já nasce isolado por empresa.
- **O app Flutter grava as rotas de GPS só no SQLite do aparelho.** Não tem login, API nem sincronização. Para conversar com o SaaS, vai precisar de um token atrelado a uma empresa e de IDs gerados no próprio aparelho (UUID).
- **A arquitetura:** um banco único, com a coluna `tenant_id` em toda tabela que pertence a uma empresa, implementado no próprio código, sem pacote de tenancy. A empresa atual vem do usuário logado: pela sessão na web, em `/app/*`, e por um token Sanctum no app. A equipe do SaaS usa a **Central**, em `/central/*`.
- **Os padrões de `.projects`** (Controller → Service → Repository, pages magras com composables, separação Central/Tenant) são aplicados sobre o Inertia, com TypeScript ([doc 08](08-padroes-de-arquitetura.md)).

## Documentos

| Arquivo | Conteúdo |
|---|---|
| [01-diagnostico-atual.md](01-diagnostico-atual.md) | O que existe hoje no SAAS e no APP, e os problemas encontrados |
| [02-estrategia-multi-tenant.md](02-estrategia-multi-tenant.md) | Modelo de isolamento, pacote ou código próprio, como identificar a empresa, usuários, papéis e Central |
| [03-modelo-de-dados.md](03-modelo-de-dados.md) | Quais tabelas são globais e quais são por empresa, tabelas novas e convenções de migration |
| [04-implementacao-backend.md](04-implementacao-backend.md) | Peças de código: contexto, escopo global, middleware, permissões, auditoria, filas, arquivos e Inertia |
| [05-api-e-app-mobile.md](05-api-e-app-mobile.md) | Autenticação do app, token por empresa e sincronização offline |
| [06-seguranca-e-testes.md](06-seguranca-e-testes.md) | Por onde dados podem vazar entre empresas e os testes que impedem isso |
| [07-plano-de-implementacao.md](07-plano-de-implementacao.md) | Fases, com checklist e critério de pronto |
| [08-padroes-de-arquitetura.md](08-padroes-de-arquitetura.md) | Como os padrões de `.projects` se aplicam ao Inertia e ao multi-tenant |
| [VALIDATION.md](VALIDATION.md) | Contrato de validação backend ↔ frontend (adicionado pelo time). Aplicação no Inertia: [doc 08, seção 4](08-padroes-de-arquitetura.md#4-contrato-de-validação-validationmd) |

## Decisões tomadas

| # | Tema | Decisão | Quando | Detalhe |
|---|---|---|---|---|
| 1 | Isolamento | Banco único com `tenant_id` | Recomendação | [02](02-estrategia-multi-tenant.md#1-modelo-de-isolamento) |
| 2 | Pacote | Código próprio, sem stancl/tenancy nem spatie/laravel-multitenancy | Recomendação | [02](02-estrategia-multi-tenant.md#2-pacote-ou-código-próprio) |
| 3 | Identificação na web | Pela sessão (`users.current_tenant_id`), telas em `/app/*`, sem subdomínio | 15/10/2026 | [02](02-estrategia-multi-tenant.md#3-como-identificar-a-empresa) |
| 4 | Identificação no app | Token Sanctum com coluna `tenant_id` | Recomendação | [05](05-api-e-app-mobile.md) |
| 5 | Usuário ↔ empresa | Muitos para muitos, com e-mail único global | 15/10/2026 | [02](02-estrategia-multi-tenant.md#4-usuários-e-pertencimento) |
| 6 | Criação de empresas | Cadastro público com teste **e** criação pela Central | 15/10/2026 | [02](02-estrategia-multi-tenant.md#5-cadastro-e-convites) |
| 7 | Papéis | spatie/laravel-permission com `teams = true`, chave `tenant_id` | Recomendação | [04](04-implementacao-backend.md#7-papéis-e-permissões-spatie-com-teams) |
| 8 | Falha sem empresa definida | Fechar e avisar: consulta sem empresa lança exceção | Recomendação | [04](04-implementacao-backend.md#3-escopo-global-tenantscope) |
| 9 | Chaves | `bigint` no que é só web, UUID v7 no que nasce no app | Recomendação | [03](03-modelo-de-dados.md#4-convenções-para-tabelas-de-empresa) |
| 10 | Frontend | Inertia mantido, padrões de `.projects` adaptados, com TypeScript | 15/10/2026 | [08](08-padroes-de-arquitetura.md) |
| 11 | Pasta `app/Tenancy` | Aprovada | 15/10/2026 | [04](04-implementacao-backend.md#1-visão-geral) |
| 12 | Planos | Feito em 15/10/2026 (fase 6): `plans`, `tenants.plan_id` e telas da Central. Faturas depois | 15/10/2026 | [02](02-estrategia-multi-tenant.md#6-ciclo-de-vida-da-empresa) |
| 13 | Nomes no código | Inglês no código, português na interface, como nos exemplos de `.projects` | Padrões de `.projects` | [08](08-padroes-de-arquitetura.md) |
| 14 | Testes do front | Vitest para `Utils` e `Composables` | 15/10/2026 | [08](08-padroes-de-arquitetura.md#1-o-que-muda-em-relação-a-projects) |
| 15 | API do app | Sanctum, token por empresa com validade de 30 dias. No app: `http`, `flutter_secure_storage` e `uuid` | 15/10/2026 | [05](05-api-e-app-mobile.md) |
| 16 | Central sem *impersonate* | Por ora a equipe usa só o painel da Central. Ver como a empresa vê fica para quando houver pedido, com auditoria | 15/10/2026 | [02](02-estrategia-multi-tenant.md#central-equipe-do-saas) |
| 17 | Limites por plano | Guardados no plano e exibidos na Central, sem fiscalização até a cobrança existir | 15/10/2026 | [03](03-modelo-de-dados.md) |
| 18 | Clientes B2B | Cadastro próprio na Central (razão social, fantasia, CNPJ e contato); a empresa nasce vinculada a um cliente (`tenants.b2b_client_id`) | 15/10/2026 | [03](03-modelo-de-dados.md) |

## Decisões em aberto

1. **Catálogo de módulos e inversores:** global, mantido pela Central, ou cada empresa cadastra o seu? A proposta é global com cadastro próprio opcional. *(Módulo de sistemas)*
2. **Faturas:** entram quando a cobrança existir. *(Depois da Central)*
