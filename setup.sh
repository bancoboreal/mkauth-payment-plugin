#!/bin/bash

# ==========================================
#   Instalador Automático Boreal Pay
#   Baixa direto do GitHub
# ==========================================

# URL do Repositório
REPO_URL="https://github.com/bancoboreal/mkauth-payment-plugin/archive/refs/heads/main.zip"

# Diretórios do Sistema
TEMP_DIR="/tmp/boreal_install_temp"
TARGET_DIR="/opt/mk-auth/admin/addons/boreal-pay"
ADDONS_DIR="/opt/mk-auth/admin/addons"

echo "===================================================="
echo "   INICIANDO INSTALAÇÃO BOREAL PAY"
echo "===================================================="

# 1. Verifica Permissão de Root
if [ "$EUID" -ne 0 ]; then
  echo "ERRO: Por favor, execute como root (sudo ./install_boreal.sh)"
  exit 1
fi

# 2. Prepara Ambiente Temporário
echo "[+] Limpando temporários..."
rm -rf "$TEMP_DIR"
mkdir -p "$TEMP_DIR"

# 3. Baixa o Arquivo ZIP
echo "[+] Baixando arquivos do GitHub..."
if command -v wget >/dev/null 2>&1; then
    wget -qO "$TEMP_DIR/repo.zip" "$REPO_URL"
elif command -v curl >/dev/null 2>&1; then
    curl -L -o "$TEMP_DIR/repo.zip" "$REPO_URL"
else
    echo "ERRO: Nem 'wget' nem 'curl' encontrados. Instale um deles para continuar."
    exit 1
fi

if [ ! -s "$TEMP_DIR/repo.zip" ]; then
    echo "ERRO: O download falhou ou o arquivo está vazio."
    exit 1
fi

# 4. Instala Dependência (Unzip) se necessário
if ! command -v unzip >/dev/null 2>&1; then
    echo "[+] Instalando 'unzip'..."
    apt-get update -qq && apt-get install -y -qq unzip
fi

# 5. Extrai o Arquivo
echo "[+] Extraindo pacote..."
unzip -q "$TEMP_DIR/repo.zip" -d "$TEMP_DIR/extracted"

# 6. Localiza a pasta correta (boreal-pay)
# O find procura a pasta 'boreal-pay' dentro do que foi extraído, independente do nome da branch
SOURCE_PATH=$(find "$TEMP_DIR/extracted" -type d -name "boreal-pay" | head -n 1)

if [ -z "$SOURCE_PATH" ]; then
    echo "ERRO: Pasta 'boreal-pay' não encontrada dentro do ZIP."
    echo "Estrutura encontrada:"
    ls -R "$TEMP_DIR/extracted"
    exit 1
fi

echo "[+] Arquivos localizados em: $SOURCE_PATH"

# 7. Instalação dos Arquivos
echo "[+] Copiando arquivos para o MK-Auth..."

# Cria diretório de destino se não existir
if [ ! -d "$TARGET_DIR" ]; then
    mkdir -p "$TARGET_DIR"
fi

# Copia o Backend (pasta boreal)
if [ -d "$SOURCE_PATH/boreal" ]; then
    cp -R "$SOURCE_PATH/boreal" "$TARGET_DIR/"
else
    echo "ERRO CRÍTICO: Pasta 'boreal' (backend PHP) não encontrada."
    exit 1
fi

# Copia o Frontend (JS) para a raiz de addons
if [ -f "$SOURCE_PATH/addon_boreal.js" ]; then
    cp "$SOURCE_PATH/addon_boreal.js" "$ADDONS_DIR/addon_boreal.js"
    echo "    Javascript do menu instalado."
else
    echo "ERRO: Arquivo 'addon_boreal.js' não encontrado."
    exit 1
fi

# 8. Ajuste de Permissões
echo "[+] Ajustando permissões (www-data)..."
chown -R www-data:www-data "$TARGET_DIR"
chown www-data:www-data "$ADDONS_DIR/addon_boreal.js"
chmod -R 755 "$TARGET_DIR"
chmod 644 "$ADDONS_DIR/addon_boreal.js"

# 9. Instalação do Banco de Dados
echo "[+] Atualizando Banco de Dados..."
DB_INSTALLER="$TARGET_DIR/boreal/instalar.php"
if [ -f "$DB_INSTALLER" ]; then
    # Executa o script PHP silenciosamente
    php -f "$DB_INSTALLER" > /dev/null 2>&1
    if [ $? -eq 0 ]; then
        echo "    Tabelas verificadas com sucesso."
    else
        echo "    AVISO: Ocorreu um erro ao rodar o script SQL. Verifique manualmente."
    fi
else
    echo "    AVISO: Script instalar.php não encontrado."
fi

# 10. Configuração do Cron Job
echo "[+] Configurando Agendamento (Cron)..."
CRON_SCRIPT="$TARGET_DIR/boreal/cron.baixa.php"

# Remove entradas antigas para evitar duplicidade
crontab -l 2>/dev/null | grep -v "boreal/cron.baixa.php" | crontab -

# Adiciona a nova entrada
(crontab -l 2>/dev/null; echo "*/5 * * * * /usr/bin/php -q $CRON_SCRIPT") | crontab -
echo "    Verificação de pagamentos agendada (5 min)."

# 11. Limpeza
rm -rf "$TEMP_DIR"

echo ""
echo "===================================================="
echo "   INSTALAÇÃO CONCLUÍDA COM SUCESSO!"
echo "===================================================="
echo "1. Aceda ao MK-Auth e pressione CTRL + F5."
echo "2. O menu 'Boreal Pay' deve aparecer no topo."
echo "3. Configure suas credenciais em: Boreal Pay > Configurações."
echo "===================================================="