# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

- **Coordenador (enrollment coordination):** abre processos RED do curso que coordena, cadastra alunos, acompanha todos os processos do curso e finaliza quando todas as atividades estão validadas.
- **Professor:** vê apenas os processos onde foi vinculado; cria/edita/valida atividades por disciplina; devolve resolução com comentário obrigatório.
- **Aluno:** vê apenas o próprio processo; envia resolução em PDF para cada atividade.
- **CTI (admin):** poder total; cadastra coordenadores e professores.
- Login único por prontuário + senha; um usuário pode ter mais de um papel (ex.: coordenador e professor).

## Product Purpose

AVA/RED do IFSP Campus Jacareí: otimizar o processo de Regime de Exercícios Domiciliares. Sucesso = coordenador, professor e aluno conduzem um processo de abertura à finalização sem papel, com validações rastreáveis.

## Positioning

Fluxo único e centralizado para o RED do campus: 1 processo = 1 aluno + N disciplinas/professores, com ciclo atividade → resolução em PDF → validação → finalização.

## Operating Context

- Fluxo RED: aluno pede RED à secretaria (fora do sistema); secretaria repassa ao coordenador; aí o sistema entra. Secretaria não tem login.
- Uso administrativo acadêmico, desktop-first, uso interno do campus.
- Sessões de trabalho front-end: dados mockados nas telas; back-end avança depois.

## Capabilities and Constraints

- Front-end apenas (responsabilidade explícita do trabalho atual); back-end é orientado/apoiado, não alterado sem combinação.
- Stack: Laravel 13 + Livewire 4 + Blaze + Tailwind 4 + Pest/Pint, PHP 8.3+.
- Papéis no banco: `cti | coordinator | professor | student` (tabela `roles`, pivot `role_user`, many-to-many).
- Prontuário é identificador de login (único, não e-mail/RA).
- Tabelas RED (processos, atividades, resoluções, vínculos) ainda não existem — conteúdo de processos em telas é mockado; dados de usuário/logado são reais.
- Status do ciclo: pendente → enviada → validada / devolvida → processo finalizado (finalização bloqueia novos envios).
- Sem auto-cadastro: CTI cadastra coordenadores/professores; coordenador cadastra alunos.
- Diretriz: protótipo `opencode_src/ava/sistema-red.html` é base visual, refatorado para Blade + componentes (não copiar HTML monolítico).

## Brand Commitments

- Nome: "Sistema RED — IFSP Campus Jacareí".
- Institucional IFSP: verde #006437 / #005c33, vermelho #d12a2e, cinza-escuro #172019, fundo claro #f2f5f1; dark mode suportado.
- Logos: `opencode_src/ava/src/imports/IFlogo.png`, `IFlogo-deitado.png`.
- Voz: direta, institucional, em português do Brasil.

## Evidence on Hand

- Protótipo de login: `opencode_src/ava/sistema-red.html`.
- Contexto consolidado do produto: `opencode_src/ava_context.txt`.
- Login real implementado: `resources/views/login.blade.php` + `LoginController`.
- Seed de papéis/usuários: `database/seeders/RoleSeeder.php`, `DatabaseSeeder` (senha `password`).
- Nenhuma tela de dashboard real ainda; nenhum dado RED real — não fabricar em produção.

## Product Principles

1. Papel define a visão: cada usuário vê apenas o que lhe cabe.
2. Fluxo do processo é a espinha dorsal da interface.
3. Sem papel no processo: clareza sobre pendências e próximos passos.
4. Front-end evoluível para dados reais sem reescrever telas.

## Accessibility & Inclusion

- Boas práticas básicas: contraste AA, foco visível, labels associados. WCAG 2.2 AA formal: não estabelecido (decisão em aberto).
