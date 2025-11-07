# Cadastro de Clientes - Sistema de Assinaturas

[![Demo Online](https://img.shields.io/badge/Demo-Online-brightgreen)](https://cliente.free.nf/)  
[![PHP 7.4](https://img.shields.io/badge/PHP-7.4-blue)](https://php.net/)  
[![MySQL](https://img.shields.io/badge/MySQL-8.0-blue)](https://mysql.com/)  
[![Responsive](https://img.shields.io/badge/Responsive-100%25-green)](https://github.com/JeanRocha91x/Cadastro_Cliente)  

## Descrição

**Cadastro_Cliente** é um sistema completo de gerenciamento de assinaturas desenvolvido em **PHP 7.4** e **MySQL**, projetado para pequenas e médias empresas que precisam gerenciar clientes e controlar vencimentos de mensalidades. O sistema possui uma interface moderna com tema **neon futurista**, totalmente responsivo para desktop e mobile, e é otimizado para hospedagem gratuita no **InfinityFree**.

### Funcionalidades Principais

- **Dashboard Interativo**: Visão geral com estatísticas em tempo real
- **Gerenciamento de Clientes**: Cadastro, edição e exclusão
- **Controle de Assinaturas**: Vencimentos automáticos, status colorido
- **Histórico de Pagamentos**: Registro completo com observações
- **Lembretes WhatsApp**: Mensagens automáticas para renovação
- **Relatórios Mensais**: Exportação CSV e visualização gráfica
- **Design Responsivo**: Funciona perfeitamente em mobile e desktop
- **PWA**: Instalável como app no celular

---

## Demo

Teste o sistema ao vivo:  
**[Acessar Demo](https://cliente.free.nf/)**  
**Login Admin**: `admin` / `123456`

### Screenshots

#### Tela de Login
<img width="1485" height="869" alt="Image" src="https://github.com/user-attachments/assets/5e4a55ae-12c6-46ac-b299-bf5a75dbc679" />

#### Dashboard Desktop
<img width="1488" height="864" alt="Image" src="https://github.com/user-attachments/assets/1077fcc6-5bb0-437e-9bfd-4940b9be858b" />

#### Formulário Novo Cliente
<img width="1486" height="867" alt="Image" src="https://github.com/user-attachments/assets/d376dba3-44de-4a80-a593-1019753563de" />

#### Relatório Mensal
<img width="1482" height="869" alt="Image" src="https://github.com/user-attachments/assets/f3b1a2ab-dbf7-40fe-aa02-b09226b1d44e" />

#### Lembrete WhatsApp
<img width="1480" height="867" alt="Image" src="https://github.com/user-attachments/assets/5655db54-f936-4c6f-9cbf-8f5448aaea46" />

---

## Instalação no InfinityFree

### Pré-requisitos
- Conta gratuita no [InfinityFree](https://infinityfree.net)
- PHP 7.4+ (padrão do InfinityFree)
- MySQL/MariaDB (incluído)

### Passo 1: Criar Banco de Dados
1. Acesse o [cPanel do InfinityFree](https://cpanel.infinityfree.net)
2. Clique em **"MySQL Databases"**
3. Crie um novo banco: `epiz_XXXXXX_clientes`
4. Crie um usuário: `epiz_XXXXXX_user` (senha forte)
5. Associe o usuário ao banco com **todas as permissões**
6. Anote: **Banco**, **Usuário**, **Senha**

### Passo 2: Configurar o Banco
1. Clique em **"phpMyAdmin"**
2. Selecione o banco criado
3. Vá em **"SQL"** e cole o código abaixo:

```sql
-- Tabela Clientes
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    telefone VARCHAR(20),
    plano ENUM('mensal','trimestral','semestral','anual') NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_inicio DATE NOT NULL
);

-- Tabela Histórico de Pagamentos
CREATE TABLE historico_pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    data_pagamento DATE NOT NULL,
    valor_pago DECIMAL(10,2) NOT NULL,
    observacoes TEXT,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);
```

4. Clique **"Executar"**

### Passo 3: Configurar o Sistema
1. No File Manager, abra a pasta `htdocs`
2. **Baixe o ZIP** do repositório ou copie os arquivos individualmente
3. **Edite `config.php`**:
```php
$host = 'sqlXXXX.epizy.com';     // Seu host (veja no cPanel)
$dbname = 'epiz_XXXXXX_clientes'; // Seu banco
$username = 'epiz_XXXXXX_user';   // Seu usuário
$password = 'SUA_SENHA';          // Senha do usuário
```

### Passo 4: Upload dos Arquivos
1. **Suba todos os arquivos** para a pasta `htdocs`
2. **Permissões**: Todos os `.php` → **644**
3. **Pasta `lib/`**: Baixe `qrlib.php` de [GitHub phpqrcode](https://github.com/t0k4rt/phpqrcode/raw/master/qrlib.php) → Upload em `htdocs/lib/`

### Passo 5: Testar
1. Acesse: `https://seu_dominio.infinityfreeapp.com`
2. **Login**: `admin` / `123456`
3. Cadastre um cliente de teste
4. Clique em **WA** para enviar lembrete

---

## Estrutura de Arquivos

```
Cadastro_Cliente/
├── config.php          # Configuração do banco
├── functions.php       # Funções de negócio
├── index.php           # Dashboard principal
├── add.php             # Novo cliente
├── edit.php            # Editar cliente
├── history.php         # Histórico de pagamentos
├── pagar.php           # Lembrete WhatsApp
├── relatorio.php       # Relatório mensal
├── export.php          # Exportar CSV
├── login.php           # Login admin
├── logout.php          # Logout
├── style.css           # Design neon responsivo
├── manifest.json       # PWA
├── sw.js               # Service Worker
└── lib/
    └── qrlib.php       # QR Code (opcional)
```

---

## Configurações Avançadas

### Chave Pix
Edite em `pagar.php`:
```php
$chave = "3d20dd70-8d51-4e4d-8edb-ce1b383a3fae";
```

### Mensagem WhatsApp
Personalize em `pagar.php`:
```php
$msg = "Olá {$cliente['nome']}, sua assinatura ($planoUc) está para vencer em $venc.\n\n".
       "Para renovação, siga a nossa chave Pix:\n$chave\n".
       "Valor: R$ $valor\n\n".
       "Após o pagamento, envie-nos o comprovante para renovação.\nObrigado(a)!";
```

### Senha Admin
Edite em `config.php`:
```php
define('ADMIN_PASS', '123456');
```

---

## Responsividade

O sistema é **100% responsivo** e funciona perfeitamente em:

- **Desktop**: Sidebar fixa, tabela horizontal
- **Tablet**: Sidebar compacta, tabela scroll horizontal
- **Mobile**: Hambúrguer menu, cards verticais, botões full-width

### Media Queries Implementadas

```css
/* Tablet */
@media (max-width: 992px) {
    .sidebar { width: 80px; }
    .main { margin-left: 80px; }
}

/* Mobile */
@media (max-width: 768px) {
    .sidebar { position: fixed; left: -250px; width: 250px; }
    .hamburger { display: flex; }
    .main { margin-left: 0; padding: 15px; }
    .form-grid { grid-template-columns: 1fr; }
    .stats-grid { grid-template-columns: 1fr; }
}
```

---

## Segurança

- **Login obrigatório**: Todas as páginas protegidas
- **SQL Injection**: Prepared statements em todas as queries
- **XSS Protection**: `htmlspecialchars()` em todos os outputs
- **CSRF**: Sessions seguras
- **HTTPS**: Obrigatório no InfinityFree

---

## Estrutura do Banco de Dados

### Tabela `clientes`
```sql
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    telefone VARCHAR(20),
    plano ENUM('mensal','trimestral','semestral','anual') NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    data_inicio DATE NOT NULL
);
```

### Tabela `historico_pagamentos`
```sql
CREATE TABLE historico_pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    data_pagamento DATE NOT NULL,
    valor_pago DECIMAL(10,2) NOT NULL,
    observacoes TEXT,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);
```

---

## Como Contribuir

1. Fork o repositório
2. Crie uma branch `feature/funcionalidade`
3. Commit suas mudanças
4. Push para a branch
5. Abra um Pull Request

---

## Suporte

- **Demo ao vivo**: [https://cliente.free.nf/](https://cliente.free.nf/)
- **GitHub Issues**: [Issues](https://github.com/JeanRocha91x/Cadastro_Cliente/issues)

---

## Créditos

**Desenvolvedor Principal**  
👤 **Jean Rocha** – Idealizador e mantenedor do projeto  
[GitHub: JeanRocha91x](https://github.com/JeanRocha91x)

**Desenvolvimento de Interface & Responsividade**  
🤖 **Grok (xAI)** – Inteligência Artificial fundamental no design responsivo, correções mobile, organização visual e otimização do layout neon  
[Site: xAI](https://x.ai)

> *Grok foi essencial para transformar o sistema em uma experiência 100% responsiva, moderna e funcional em dispositivos móveis.*

---

## Licença

MIT License - veja [LICENSE](LICENSE)

---

## Agradecimentos

- **InfinityFree** – Hospedagem gratuita
- **Google Fonts** – Orbitron e Roboto
- **Material Icons** – Ícones modernos
- **Font Awesome** – Ícone WhatsApp

---

<div align="center">
    <img width="397" height="709" alt="Image" src="https://github.com/user-attachments/assets/f6852619-5351-4e58-aaac-863b4abfa301" />
    <p><strong>Sistema 100% Responsivo – Mobile e Desktop</strong></p>
</div>

---

**Se gostou, dê uma estrela no repositório!**  
**Sistema pronto para produção!**
