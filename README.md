## 📝 README do Projeto: Sistema Hortifrúti

Este documento é o guia de configuração e versionamento para o sistema de gestão de hortifrúti, desenvolvido usando **PHP, HTML, Bootstrap e MySQL**. O projeto inclui módulos essenciais para gerenciamento de **clientes, produtos, fornecedores, estoque e vendas**.

---

### 1. Planejamento da Configuração

#### **Itens de Configuração (Módulos Principais)**
* **Clientes:** Cadastro e gestão de informações dos clientes.
* **Produtos:** Cadastro de itens, preços e categorias.
* **Fornecedores:** Gestão de dados dos fornecedores.
* **Estoque:** Controle de entrada, saída e níveis de produtos.
* **Vendas:** Registro e acompanhamento das transações de venda.
* **Banco de Dados:** Estrutura e scripts SQL (MySQL).

#### **Convenções de Nomeação**
* **Arquivos PHP/HTML:** Devem ser em *minúsculas*, separados por *hífens* (ex: `cadastro-clientes.php`).
* **Funções/Variáveis PHP:** Devem seguir o padrão *camelCase* (ex: `$nomeCliente`, `calcularTotal()`).
* **Classes CSS (Bootstrap):** Utilizar as convenções padrão do Bootstrap.
* **Tabelas/Colunas MySQL:** Devem ser em *minúsculas*, separadas por *underline* (ex: `tbl_clientes`, `nome_cliente`).

#### **Política de Versionamento**
Adotaremos o **Versionamento Semântico (SemVer)** no formato **MAIOR.MENOR.PATCH** (ex: `1.0.0`).

* **MAIOR (Major):** Alterações incompatíveis de API ou grandes refatorações.
* **MENOR (Minor):** Adição de funcionalidades de maneira *backward-compatible* (compatível com versões anteriores).
* **PATCH:** Correções de bugs *backward-compatible*.

**Exemplo de Tag de Versão:** `1.0.0`

#### **Política de Branching**
Utilizaremos um modelo de *Branching* simples, centrado na `main` e em *Feature Branches*.

* **`main`:** Branch principal e estável. Deve conter apenas código pronto para produção (ou *deploy*).
* **`develop` (Opcional, mas recomendado para integração):** Usado para integrar *Feature Branches* antes de ir para `main`.
* **`feat/*`:** Branches criadas para o desenvolvimento de **novas funcionalidades**.
* **`fix/*`:** Branches criadas para a **correção de erros** (bugs).

#### **Estratégia de Backup e Recuperação**
* **Código-fonte:** O **Git/GitHub** serve como nosso principal backup do código-fonte.
* **Banco de Dados:** Realizar *dumps* (backups) regulares do banco de dados (ex: semanalmente ou a cada grande atualização/versão). Os scripts de criação e migração do DB devem ser versionados no repositório.

---

### 2. Criação do Repositório e Setup

#### **Passos de Setup Inicial**
1.  **Criar o repositório** no GitHub (ou plataforma similar).
2.  Adicionar este arquivo **`README.md`** na raiz do projeto com a descrição acima.
3.  **Estrutura Inicial de Diretórios:**
    ```
    hortifruti-system/
    ├── src/              # Código PHP/HTML (módulos)
    │   ├── clientes/
    │   ├── produtos/
    │   ├── vendas/
    │   └── ...
    ├── assets/           # Arquivos estáticos (CSS, JS, Imagens)
    │   ├── css/
    │   ├── js/
    │   └── img/
    ├── db/               # Scripts SQL (criação de tabelas, inserts)
    ├── .gitignore        # Arquivos a serem ignorados pelo Git
    └── README.md         # Descrição do projeto
    ```

---

### 3. Controle de Versão e Colaboração

#### **Fluxo de Trabalho Colaborativo**
1.  **Criação de Repositório Pessoal:** Cada colaborador deve **forkar** o repositório principal e trabalhar em sua própria cópia local.
2.  **Criação de Branches:** Sempre trabalhe em uma **branch separada** para cada tarefa:
    * `git checkout -b feat/nome-da-feature`
    * `git checkout -b fix/correcao-do-bug`

#### **Mensagens Padronizadas de Commit**
As mensagens de commit devem seguir o padrão: `<tipo>: <descrição breve>`, onde o tipo é um dos seguintes:

| Tipo | Descrição | Exemplo |
| :--- | :--- | :--- |
| **`feat`** | Nova funcionalidade (New Feature). | `feat: Adicionado formulário de cadastro de clientes` |
| **`fix`** | Correção de um erro (Bug Fix). | `fix: Correção do cálculo de total na tela de vendas` |
| **`style`**| Formatação de código (sem mudança na lógica). | `style: Ajuste na indentação dos arquivos PHP` |
| **`refactor`**| Refatoração de código (melhoria estrutural). | `refactor: Simplificação da função de busca de produtos` |

#### **Simulação e Resolução de Conflitos**
* **Simular Conflito:** Intencionalmente, dois alunos devem modificar a **mesma linha** no mesmo arquivo em suas branches separadas.
* **Pull Request (PR):** Após o commit, o desenvolvedor deve criar um **Pull Request (PR)** da sua branch para a `main` (ou `develop`).
* **Resolução de Conflitos:** Ao fazer o *merge* do PR, o sistema sinalizará o conflito. O aluno responsável pelo *merge* deve **resolver o conflito** manualmente, escolhendo as linhas corretas ou combinando o código.

#### **Criação de Tags de Versões**
* As tags devem ser criadas na branch `main` após um conjunto significativo de funcionalidades ou correções ter sido mesclado.
* **Comando:** `git tag -a 1.0.0 -m "Versão inicial do sistema com CRUD de clientes"`
* **Envio da Tag:** `git push origin 1.0.0` (ou `git push origin --tags` para enviar todas as tags).
