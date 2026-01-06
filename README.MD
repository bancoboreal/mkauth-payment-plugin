# Boreal Pay - Plugin de Pagamentos para MK-Auth

Integração oficial do gateway de pagamentos **Boreal** para o sistema **MK-Auth**. Este plugin permite gerar Boletos e Pix com baixa automática diretamente pelo painel do provedor e central do assinante.

## 🚀 Funcionalidades

- **Pix Híbrido/Nativo:** Gera QR Code e código "Copia e Cola" instantaneamente.
- **Boleto Bancário:** Redirecionamento automático para o PDF do boleto.
- **Baixa Automática:**
  - **Webhook (IPN):** Baixa em tempo real assim que o pagamento é confirmado.
  - **Cron Job:** Varredura automática de segurança a cada 5 minutos.
- **Interface Integrada:** Botões injetados nativamente na tela de detalhes do cliente.
- **Logs Detalhados:** Histórico completo de comunicação com a API para auditoria.

## 📋 Pré-requisitos

- MK-Auth (Versão 24.x ou superior recomendada).
- Acesso SSH (Root) ao servidor MK-Auth.
- Credenciais de API da Boreal (Client ID, Secret e Token).

## 📦 Instalação Rápida

Acesse o terminal do seu servidor MK-Auth e execute **apenas um** dos comandos abaixo:

**Via curl:**
```bash
curl -sL https://raw.githubusercontent.com/bancoboreal/mkauth-payment-plugin/main/setup.sh | bash

```

**Ou via wget:**

```bash
wget -qO- https://raw.githubusercontent.com/bancoboreal/mkauth-payment-plugin/main/setup.sh | bash

```

*O instalador irá baixar os arquivos, configurar o banco de dados, ajustar permissões e configurar o agendamento (cron) automaticamente.*

## ⚙️ Configuração

Após a instalação:

1. Acesse o painel administrativo do MK-Auth.
2. Limpe o cache do navegador (**CTRL + F5**).
3. No menu superior, localize e clique em **Boreal Pay** > **Configurações**.
4. Preencha os campos:
* **Client ID** (Sua identificação de Workspace)
* **Client Secret** (Sua chave de Webhook)
* **Token** (Seu Token de API Bearer)
* **Ambiente:** Selecione `Produção` para valer.


5. Marque a opção **Ativar Integração**.
6. Clique em **Salvar**.

## 🔄 Webhook (Retorno Automático)

Para que as faturas sejam baixadas instantaneamente, configure a URL de notificação no painel da Boreal:

```text
https://SEU_DOMINIO_MKAUTH/admin/addons/boreal-pay/boreal/ipn.php

```

## 🛠 Suporte e Troubleshooting

Se o menu não aparecer após a instalação:

1. Verifique se limpou o cache do navegador.
2. Verifique os logs de instalação na pasta `/opt/mk-auth/admin/addons/boreal-pay/`.
3. Acesse **Boreal Pay > Logs** para ver erros de comunicação com a API.

---

Desenvolvido para Boreal.

```

---

### 2. O Comando de Instalação (curl / wget)

Para que o comando funcione, você deve pegar o script que criamos no passo anterior (o `install_boreal.sh`), renomeá-lo para **`setup.sh`** e colocá-lo na **raiz** do seu repositório GitHub (`main` branch).

O comando para os seus clientes ou técnicos usarem será:

**Opção 1 (Curl - Mais comum):**
```bash
curl -sL https://raw.githubusercontent.com/bancoboreal/mkauth-payment-plugin/main/setup.sh | bash

```

**Opção 2 (Wget - Alternativa segura):**

```bash
wget -qO- https://raw.githubusercontent.com/bancoboreal/mkauth-payment-plugin/main/setup.sh | bash

```

### ⚠️ Importante para o Desenvolvedor:

Certifique-se de que a estrutura do seu repositório GitHub esteja assim para que tudo funcione:

```text
/ (Raiz do Repo)
├── setup.sh           <-- O script bash que criamos anteriormente
├── README.md          <-- Este arquivo de documentação
├── addon_boreal.js    <-- O JS do menu
└── boreal/            <-- A pasta com os arquivos PHP (api.php, init.php, etc)