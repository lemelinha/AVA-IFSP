---
version: 1
slug: "resources-views-auth-home-blade-php"
primary_target: "resources/views/auth/home.blade.php"
related_targets: []
---

# Surface brief: painel do usuário logado (rota `/`)

## Scope and mode

Operate. Dashboard diário pós-login em `/` (`resources/views/auth/home.blade.php`). Usuário logado (papel real do banco), foco em pendências e ação rápida. Herda o mundo visual do login (estabelecido, sem DESIGN.md).

## Audience, job, action

Coordenador/professor/aluno/CTI no uso diário. Job: ver em 5 segundos quem é, o papel, as pendências e a próxima ação. Action: abrir processo, validar, finalizar, cadastrar — conforme `@role`/`@hasAnyRole`. Conteúdo RED mockado (sem tabelas no banco); usuário/log/papel reais. Sem requisito WCAG formal (boas práticas AA).

## Direction contract

### THESIS

O índice de processos é a própria página: varredura e comparação em uma tabela densa com filtros; recusa o dashboard-de-atalhos (grade de cards de função como estrutura principal) e a landing de boas-vindas.

### OWN-WORLD

Mundo do login: chão `#f2f5f1`, tinta `#172019`, verde IFSP `#006437` (ação/estado ok), vermelho `#d12a2e` (acento e ação crítica), cinza `#647168`, borda `#d9e1d9`, cards brancos com borda 1px e raio 12–16px, sem sombra; Instrument Sans; dark mode do login preservado; faixa vermelha de 4px no topo como assinatura.

### STORY

Usuário entra, lê contadores do período, filtra, abre o processo da linha; pendência e estado de cada processo legíveis por chip de status; papel do usuário no topo decide o que a tabela e os botões oferecem.

### FIRST VIEWPORT

1440: topbar 64px (logo + selo de papel + prontuário); cabeçalho "Processos RED" com botão "Novo processo"; barra de contadores (abertos/aguardando validação/prontos para finalizar/validadas); barra de filtros (busca, curso, status, período); tabela com 6 linhas (aluno+prontuário, curso·período, disciplinas, professor, chip de status, ação por linha). Filtros e tabela operáveis, sem ornamentação.

### FORM

Forma: índice/tabela densa com filtros — posição 5 da minha lista ordenada (dealt, escolhida pelo usuário). seed:1309834abf8367b3

### FINISH

unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance.

## Unresolved decisions

- Dados RED reais (tabelas processos/atividades/resoluções) ainda não existem: mock marcado como sintético na UI.
- Correções mínimas de back-end para a página renderizar (redirect pós-login e `view()` do controller) — pendentes de aprovação do usuário.
- Escopo mobile: tabela responsiva (cartões empilhados no mobile) por decidir no build.
