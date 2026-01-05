<?php
require_once dirname(__FILE__) . '/init.php';

$sqls = array();
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

$sqls[] = "CREATE TABLE IF NOT EXISTS boreal_logs (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    tipo varchar(20),
    mensagem text,
    payload longtext,
    data_criacao datetime,
    PRIMARY KEY (id)
)";

$erros = array();
foreach ($sqls as $sql) {
    if (!$mysqli->query($sql)) {
        $erros[] = $mysqli->error;
    }
}

if (!empty($erros)) {
    echo '<h3>Erro ao instalar Boreal Pay</h3>';
    echo '<pre>' . implode("\n", $erros) . '</pre>';
    exit;
}

echo '<h3>Boreal Pay instalado com sucesso.</h3>';
