# 01. Diagnóstico do estado atual

## 1. SAAS (este repositório)

### Stack

| Camada | Versão instalada | Observação |
|---|---|---|
| PHP | 8.4 (`composer.json` aceita `^8.3`) | |
| Laravel | 13.36.0 | Usa atributos novos como `#[Fillable]` e `#[Hidden]` no `User` |
| Inertia | inertia-laravel 3.5.1, @inertiajs/vue3 ^3.7.1 | |
| Front | Vue 3.5, Tailwind 4, daisyUI 5, Vite 8, Ziggy 2.6 | Tema `vistoria` e fontes pela Bunny (`laravel-vite-plugin/fonts`) |
| Banco | MySQL (`db_vistoria_solar`) em dev, SQLite em memória nos testes | |
| Sessão, cache e fila | driver `database` | |
| Testes | Pest 5.3 instalado | Os dois testes existentes são classes PHPUnit. Não há `tests/Pest.php` |

### Pacotes instalados e ainda não configurados

| Pacote | Versão | Papel no multi-tenant |
|---|---|---|
| spatie/laravel-permission | 8.3.0 | Papéis e permissões. Tem o recurso *teams*, que vira "papel por empresa". Config e migrations ainda não publicadas, e isso é bom: `teams` precisa ser ligado **antes** de rodar as migrations do pacote |
| owen-it/laravel-auditing | 14.0.6 | Histórico de alterações. Precisa gravar a empresa em cada registro, senão a tela de histórico mistura empresas |
| hryagstn/laravel-scalpel | 1.11.0 | Scanner de intrusão no sistema de arquivos. Não muda com tenancy |
| steppinghat/emoji-detector | 1.11.0 | Validação de texto. Não muda com tenancy |
| jaydeep/laravel-time-machine | 1.1.0 (dev) | Profiler de requisições. Ver os problemas 1 e 2 abaixo |

### O que existe

- **Model:** só `User` (`name`, `email`, `password`). Não implementa `MustVerifyEmail`, então o middleware `verified` da rota `/dashboard` deixa qualquer usuário passar.
- **Migrations:** `users`, `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`. As três migrations já rodaram no MySQL local.
- **Rotas:** `/` (Welcome), `/dashboard` (`auth` + `verified`) e as rotas de autenticação do Breeze em `routes/auth.php`. Não há `routes/api.php`, mas `bootstrap/app.php` já devolve JSON para `api/*`.
- **Middleware:** `HandleInertiaRequests` compartilha `auth.user` com o model `User` inteiro.
- **Cadastro:** `RegisteredUserController@store` cria um usuário solto (nome, e-mail e senha) e faz login.
- **Front:** páginas `Welcome`, `Dashboard` e `Auth/*`, `GuestLayout` e componentes de formulário (`FormField`, `TextInput`, `PasswordInput`, `PasswordStrength`). Não há layout autenticado: a barra do `Dashboard` está escrita direto na página.
- **Testes:** só os `ExampleTest` de Feature e Unit. Os testes de autenticação do Breeze não estão no repositório.

### Escopo do produto

O código de domínio ainda não existe, mas a página inicial (`resources/js/Constants/welcome.js`) descreve os módulos. Eles são a base do modelo de dados proposto no [doc 03](03-modelo-de-dados.md):

| Módulo | Descrição na Welcome |
|---|---|
| Clientes | Cadastro completo de clientes, contatos, endereços e sistemas |
| Módulos e inversores | Equipamentos instalados em cada cliente |
| Formulários de vistoria | Questionários personalizados, padronizando as vistorias da equipe, com fotos e anexos |
| Processos | Homologação junto à concessionária até a aprovação |
| Estoque | Entradas, saídas e histórico de materiais |
| Agenda e atividades | Calendário, kanban e atividades da equipe |
| Mapa de clientes | Localização dos sistemas e das vistorias |

## 2. APP (Flutter, `../APP`)

- Flutter com `go_router`, `mapbox_maps_flutter`, `geolocator` e `sqflite`.
- Faz uma coisa: grava o trajeto do técnico em campo e marca cada ponto como em movimento (`M`) ou pausado (`P`).
- O banco local (`lib/dados/banco_local.dart`, versão 2) tem duas tabelas:
  - `rotas`: `id` autoincremento, `iniciada_em`, `finalizada_em` e os totais.
  - `pontos_rota`: `id` autoincremento, `rota_id`, `latitude`, `longitude`, `data_hora`, `status` e `velocidade`.
- Não há login, usuário, empresa, cliente HTTP nem sincronização.

**O que isso significa para o multi-tenant:**

- Os IDs autoincremento do aparelho batem com os de outros aparelhos. Para sincronizar, cada registro precisa de um UUID gerado no aparelho.
- Um mesmo aparelho pode ser usado por outro usuário, ou por um usuário que trabalha para duas empresas. Os dados locais precisam ficar separados por empresa e usuário.

## 3. Problemas encontrados

Os três primeiros já existem hoje. Com várias empresas na mesma base, ficam mais graves.

1. **`storage/time-machine/*.json` está versionado no git** (100 arquivos). Cada arquivo guarda uma requisição inteira: SQL com os valores, payload de sessão e user agent. Com dados reais de várias empresas, isso vira um vazamento no repositório. Correção: adicionar `/storage/time-machine` ao `.gitignore` e rodar `git rm -r --cached storage/time-machine`.
2. **O painel do time-machine (`/time-machine`) só tem o middleware `web`, sem `auth`.** Ele liga sozinho sempre que `APP_DEBUG=true`. Um ambiente de homologação com dados reais e debug ligado mostraria as consultas de todas as empresas a qualquer visitante. Correção: `TIME_MACHINE_ENABLED=false` em todo ambiente que não seja a máquina do desenvolvedor.
3. **`auth.user` compartilha o model inteiro com o front.** Toda coluna nova de `users` (`current_tenant_id`, `is_central_admin`, etc.) passaria a ir para o HTML de cada página. Correção: compartilhar só os campos necessários (ver [doc 04, seção 11](04-implementacao-backend.md#11-inertia-e-front)).
4. **O cadastro cria um usuário sem empresa.** No multi-tenant, todo usuário precisa nascer dentro de uma empresa ou entrar nela por convite ([doc 02, seção 5](02-estrategia-multi-tenant.md#5-cadastro-e-convites)).
5. **`projeto-antigo` é um submódulo sem `.gitmodules`, e a pasta está vazia.** Não foi possível analisar. Se ali está o sistema anterior, vale compará-lo com o modelo de dados do [doc 03](03-modelo-de-dados.md) antes da fase 4.
6. **Não há testes de autenticação.** As fases de tenancy mexem no cadastro, no login e no middleware. Sem esses testes, uma regressão no login passa despercebida. Recuperar os testes de autenticação do Breeze (versão Pest) é o primeiro item do [plano](07-plano-de-implementacao.md).
