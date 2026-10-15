# 🎨 Identidade Visual — Guia de UI/UX do App

> **Público-alvo:** empresas do ramo de energia solar (gestão solar, instalação, monitoramento).
> **Foco de acessibilidade:** experiência pensada para pessoas com TDAH.
> **Premissa central:** *menos é mais* — interfaces que reduzem carga cognitiva convertem melhor e geram menos erros operacionais.

---

## 1. Fundamentos da marca

A identidade visual transmite os valores do setor solar: **confiança, tecnologia limpa, clareza e sustentabilidade**. O teal (verde-azulado) é a cor da marca — evoca energia limpa e calma, diferenciando-se do azul corporativo genérico.

**Tom de voz da interface:** direto, orientado a ação, sem jargões desnecessários. O usuário (gestor, técnico, dono de empresa) está ocupado — a interface deve respeitar o tempo dele.

---

## 2. Paleta de cores

Paleta extraída do sistema legado (Quasar) e padronizada em `tailwind.config.js`.

### 2.1 Cor da marca — Escala `brand` (teal)

| Token | Hex | Uso recomendado |
|---|---|---|
| `brand-50` | `#e0f2f1` | Fundos sutis de destaque, hovers claros |
| `brand-100` | `#b2dfdb` | Bordas de cards selecionados |
| `brand-300` | `#4db6ac` | Ícones secundários, gráficos |
| `brand-500` | `#009688` | Links, badges, elementos de apoio |
| **`brand-700`** | **`#00796b`** | **Cor principal da marca — botões primários, links, destaques** |
| `brand-800` | `#00695c` | Hover do botão primário |
| `brand-900` | `#004d40` | Texto de marca sobre fundos claros do brand |
| `brand-950` | `#004940` | Fundos escuros de seção |

### 2.2 Cores de apoio

| Token | Hex | Uso |
|---|---|---|
| `leaf` | `#55a38b` | Fundos de painéis laterais, gradientes |
| `leaf-light` | `#80cbc4` | Gradientes, ilustrações |
| `ink` | `#333333` | Texto principal, header/sidebar |
| `ink-soft` | `#4d4d4c` | Texto de descrições e eventos |
| `ink-muted` | `#868e96` | Placeholders, metadados |
| `ink-subtle` | `#999999` | Texto desabilitado (nunca em ações) |
| `surface` | `#f7f7f7` | Fundo de formulários |
| `surface-alt` | `#f0f3f3` | Fundo de cards secundários |
| `surface-line` | `#d9d9d9` | Bordas e divisores |
| `danger` | `#c80000` | Erros e ações destrutivas |

### 2.3 Cores de gráficos (`chart`)

`blue #008FFB` · `green #00E396` · `yellow #FEB019` · `red #FF4560` · `purple #775DD0` · `teal #00796B`

> **Regra:** em dashboards de geração solar, use `chart.green` para energia gerada e `chart.yellow` para irradiância/estimativa. Nunca codifique significado apenas por cor — sempre acompanhe com rótulo ou ícone (acessibilidade para daltônicos).

### 2.4 Contraste e acessibilidade (WCAG AA)

| Combinção | Razão de contraste | Status |
|---|---|---|
| `brand-700` text sobre branco | ≈ 4.6:1 | ✅ AA (texto normal) |
| `ink` sobre `surface` | ≈ 10:1 | ✅ AAA |
| Branco sobre `brand-700` (botão) | ≈ 4.6:1 | ✅ AA |
| `ink-muted` sobre branco | ≈ 3.9:1 | ⚠️ Apenas texto grande |
| `brand-300` texto sobre branco | ≈ 2.4:1 | ❌ Nunca usar como texto |

**Regras obrigatórias:**
- Texto normal: contraste mínimo **4.5:1** · Texto grande (≥18pt/24px): **3:1**
- Nunca usar `brand-300` ou `ink-subtle` para texto funcional
- Estados de erro não dependem só da cor: sempre ícone + mensagem

---

## 3. Tipografia

```js
fontFamily: {
    sans: ['Montserrat', 'ui-sans-serif', 'system-ui', 'sans-serif'],
    mono: ['"Oxygen Mono"', 'ui-monospace', 'monospace'],
}
```

- **Montserrat** (interface): peso 400 para texto, 500–600 para botões e labels, 700 apenas para títulos de página.
- **Oxygen Mono** (dados): kWh, R$, datas técnicas, IDs — alinhamento tabular facilita a leitura de números em tabelas de produção.

### Escala tipográfica (mobile-first)

| Elemento | Mobile | Desktop | Peso |
|---|---|---|---|
| Display / hero | 28px | 40px | 700 |
| H1 (tela) | 24px | 32px | 700 |
| H2 (seção) | 20px | 24px | 600 |
| H3 (card) | 16px | 18px | 600 |
| Corpo | 16px | 16px | 400 |
| Pequeno / legenda | 13px | 13px | 400 |
| Botão | 16px | 16px | 600 |

> **Nunca usar texto abaixo de 13px.** Pessoas com TDAH frequentemente têm dificuldade de sustentar atenção em blocos de texto densos — tamanho confortável reduz retrabalho visual.

---

## 4. Princípios de UX para pessoas com TDAH

O TDAH afeta principalmente **atenção sustentada, memória de trabalho e controle inibitório**. Cada decisão de design abaixo ataca um desses pontos.

### 4.1 Uma ação principal por tela

- Cada tela tem **exatamente um botão primário** (`btn-primary`, `bg-brand-700`).
- Ações secundárias usam estilo fantasma ou outline (`btn-outline`).
- Ação destrutiva (excluir contrato, cancelar orçamento) nunca compete visualmente com a principal.

> *Por quê:* decisão paralela paralisa. O usuário com TDAH gasta energia cognitiva escolhendo onde clicar — remova a escolha quando ela não importa.

### 4.2 Quebre tarefas longas em etapas

- Cadastro de cliente, orçamento de instalação e medição de usina: usar **wizard com barra de progresso** ("Etapa 2 de 4 — Dados técnicos").
- Sempre mostrar o progresso e permitir **voltar sem perder dados**.
- **Salvar rascunho automaticamente** a cada campo preenchido. Perder 20 minutos de formulário por um toque acidental no botão "voltar" do celular é uma experiência que destrói a confiança no app.

### 4.3 Reduza a densidade de informação

- Dashboard inicial: **máximo de 4–6 cards**. O restante vai em abas ou "Ver detalhes →".
- Não empilhe 10 gráficos na home. Prioridade para quem gere receita: produção hoje, alertas pendentes, faturas em aberto.
- Use **disclosure progressivo**: mostre o essencial, esconda o detalhe atrás de um clique.

### 4.4 Feedback imediato e visível

- Toda ação tem resposta em **< 100ms** visualmente (spinner no botão, skeleton).
- Confirmação de sucesso: toast **não-automático** para ações importantes ("Orçamento enviado ✓") com botão "Desfazer" quando aplicável.
- Erros: mensagem **ao lado do campo**, não resumida no topo onde exige memória de trabalho para correlacionar.

### 4.5 Navegação previsível e persistente

- Mesma posição de menu em todas as telas. No mobile: **bottom navigation** com 4–5 itens no máximo.
- "Você está aqui" sempre visível: título da página no topo + item ativo destacado.
- Breadcrumbs em fluxos de mais de 2 níveis.

### 4.6 Tolerância a erros e distração

- Botão "Salvar" sticky no mobile em formulários longos.
- **Desfazer > confirmar.** O modal "Tem certeza?" para cada ação é desgastante; prefira `Undo` por 5–10 segundos.
- Inputs com máscara (telefone, CNPJ, CEP), autocomplete e teclado numérico automático para campos numéricos.

### 4.7 Animação com moderação

- Animações existentes no projeto (`animate-gradient`, `animate-float`) são **decorativas** — usar apenas em telas de login/marketing, nunca em telas de trabalho.
- Respeitar `prefers-reduced-motion`: desativar gradiente animado e float para quem optar.

```css
@media (prefers-reduced-motion: reduce) {
    .animate-gradient, .animate-float { animation: none; }
}
```

### 4.8 Minimize interrupções

- Notificações no app: agrupadas em um centro de alertas, **nunca** como pop-ups sucessivos.
- Somente push para eventos críticos (usina offline, falha de geração), nunca para marketing dentro do app de trabalho.

---

## 5. Responsividade — regras obrigatórias

**Mobile-first em tudo.** O técnico de campo consulta o app no celular com luva, sol na tela e pressa.

### 5.1 Breakpoints

| Breakpoint | Largura | Alvo |
|---|---|---|
| Base | < 640px | Celular (padrão de projeto) |
| `sm` | ≥ 640px | Celular grande |
| `md` | ≥ 768px | Tablet |
| `lg` | ≥ 1024px | Desktop |

### 5.2 Regras mobile obrigatórias

1. **Área de toque mínima: 44×44px** em todos os botões, ícones e inputs clicáveis (diretriz Apple HIG / WCAG 2.5.8).
2. **Bottom navigation** com altura 64px + `padding-bottom: env(safe-area-inset-bottom)` para iPhone.
3. **CTA principal sticky** no rodapé de formulários e telas de ação.
4. Tabelas de dados (produção, faturas) viram **cards empilhados** no mobile — nunca tabela com scroll horizontal.
5. Fonte base mínima 16px em inputs (evita zoom automático no iOS).
6. Espaçamento generoso: `space-y-4` mínimo entre grupos de campos; telas respiráveis reduzem erro de toque.
7. Testar em **360px de largura** (Galaxy A, Moto G) — se funcionar aí, funciona em qualquer lugar.

### 5.3 Estrutura de layout

```
┌─────────────────────┐
│  Header (título +   │  ← sticky, altura 56px
│  ação contextual)   │
├─────────────────────┤
│                     │
│   Conteúdo          │  ← max-w por breakpoint:
│   (1 coluna mobile) │     base: full-bleed + px-4
│                     │     lg: max-w-7xl
├─────────────────────┤
│  Bottom nav (mobile)│  ← sticky, 64px + safe-area
│  ou paginação       │
└─────────────────────┘
```

---

## 6. Componentes — diretrizes rápidas

| Componente | Diretriz |
|---|---|
| **Botão primário** | `bg-brand-700 text-white font-semibold h-11 rounded-lg`, hover `bg-brand-800`. Um por tela. |
| **Botão secundário** | `border border-brand-700 text-brand-700`, transparente. |
| **Botão destrutivo** | `bg-danger text-white`, sempre com confirmação ou undo. |
| **Input** | Label sempre visível acima (nunca só placeholder), altura 48px, borda `surface-line`, foco com ring de 2px `brand-500`. |
| **Card** | `bg-white rounded-xl shadow-sm border border-surface-line p-4`. |
| **Badge/Tag** | Fundo `brand-50`, texto `brand-900`. Status: verde=ok, amarelo=atenção, vermelho=critical. |
| **Toast** | Inferior no mobile (perto do polegar), topo à direita no desktop. Máx. 3 segundos, exceto com ação "Desfazer". |
| **Empty state** | Ilustração leve + uma frase orientada + botão de ação ("Nenhuma usina cadastrada — Cadastrar primeira usina"). |

### Estado de foco visível (obrigatório)

```css
*:focus-visible {
    outline: 2px solid #4db6ac; /* brand-300 */
    outline-offset: 2px;
}
```

Pessoas com TDAH navegam muito por teclado/Tab e perdem facilmente o ponto de foco — o anel de foco nunca deve ser removido.

---

## 7. Telas-chave do app solar

| Tela | Prioridade UX |
|---|---|
| **Login** | Máximo 2 campos + "Esqueci a senha". Fundo com gradiente brand (já implementado). Sem distrações. |
| **Dashboard** | 3 KPIs grandes (geração hoje, mês, alertas) + 1 gráfico principal. Cards de alerta com ação direta. |
| **Usinas (lista)** | Cards com foto/status; busca sempre visível; filtro em sheet bottom no mobile. |
| **Detalhe da usina** | Geração em destaque; abas: Produção · Equipamentos · Financeiro. Gráficos com legenda em português claro. |
| **Alertas** | Lista ordenada por criticidade; cada item com ação de um toque ("Ver usina"). |
| **Orçamentos** | Wizard com salvamento automático; resumo final antes de enviar. |
| **Relatórios/PDF** | Botão de exportar sempre no mesmo lugar (header direita). |

---

## 8. Checklist de revisão (antes de mergear qualquer tela)

- [ ] Há **um único botão primário** visível?
- [ ] Todos os alvos de toque têm ≥ 44px?
- [ ] Contraste de todo o texto ≥ 4.5:1?
- [ ] Estados de vazio, carregamento e erro estão desenhados?
- [ ] O formulário salva rascunho / tolera interrupção?
- [ ] Animações respeitam `prefers-reduced-motion`?
- [ ] Foco de teclado é visível em todos os elementos interativos?
- [ ] Testado em 360px de largura?
- [ ] Nenhuma informação depende exclusivamente de cor?

---

*Documento gerado a partir da paleta oficial (`tailwind.config.js`) — versão 1.0 · manter sincronizado com o tema do daisyUI em `app.css`.*
