<?php
require_once dirname(__FILE__) . '/init.php';

$sqls = array();

// Criação da tabela de configuração
$sqls[] = "CREATE TABLE IF NOT EXISTS boreal_config (
    id int(11) NOT NULL AUTO_INCREMENT,
    client_id text,
    client_secret text,
    token text,
    ambiente varchar(20),
    ativo tinyint(1) DEFAULT 1,
    baixa_automatica int(10),
    PRIMARY KEY (id)
)";

// Criação da tabela de faturas
$sqls[] = "CREATE TABLE IF NOT EXISTS boreal_faturas (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    fatura_mkauth bigint(20),
    txid varchar(100),
    status varchar(30),
    valor float(10,2),
    pix_copia_cola text,
    linha_digitavel text,
    pdf_url text,
    data_criacao datetime,
    PRIMARY KEY (id)
)";

// Criação da tabela de logs
$sqls[] = "CREATE TABLE IF NOT EXISTS boreal_logs (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    tipo varchar(20),
    mensagem text,
    payload longtext,
    data_criacao datetime,
    PRIMARY KEY (id)
)";

// CORREÇÃO:
// Usamos INSERT IGNORE para inserir a configuração inicial (ID 1).
// Se o ID 1 já existir, o MySQL ignora este comando e não gera erro.
$sqls[] = "INSERT IGNORE INTO boreal_config (id, ativo, baixa_automatica, ambiente) VALUES (1, 0, 1, 'sandbox')";

$erros = array();
foreach ($sqls as $sql) {
    if (!$mysqli->query($sql)) {
        // Exibe o erro específico e qual comando falhou para facilitar o debug
        $erros[] = "<b>Erro:</b> " . $mysqli->error . "<br><b>SQL:</b> " . $sql;
    }
}

if (!empty($erros)) {
    echo '<h3>Erro ao instalar Boreal Pay</h3>';
    echo '<div style="background-color:#ffebe6; padding:15px; border:1px solid #ff5c33; border-radius:5px;">';
    echo implode("<hr>", $erros);
    echo '</div>';
    exit;
}

echo '<h3 style="color: green;">Boreal Pay instalado com sucesso.</h3>';
echo '<p>Tabelas criadas/verificadas. Pode aceder às configurações.</p>';
?>