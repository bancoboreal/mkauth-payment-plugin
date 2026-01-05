<?php
define('BOREAL_PUBLIC', true);
require_once dirname(__FILE__) . '/init.php';
require_once dirname(__FILE__) . '/bancos/boreal.php';

$config = boreal_get_config($mysqli);
if ((int) $config['ativo'] !== 1 || (int) $config['baixa_automatica'] !== 1) {
    echo "Boreal Pay desativado.\n";
    exit;
}

$banco = new BorealBank($config, $mysqli);
$sql = "SELECT * FROM boreal_faturas WHERE status != 'pago' AND status != 'paid' ORDER BY data_criacao DESC LIMIT 20";
$result = $mysqli->query($sql);

if (!$result) {
    echo "Erro ao buscar faturas.\n";
    exit;
}

while ($row = $result->fetch_assoc()) {
    $retorno = $banco->baixa($row['txid']);
    if (!$retorno['sucesso']) {
        boreal_log($mysqli, 'erro', 'Falha na baixa automatica.', $retorno);
        continue;
    }

    $status = strtolower($retorno['status']);
    if ($status === 'paid' || $status === 'pago') {
        $stmt = $mysqli->prepare("UPDATE boreal_faturas SET status = ? WHERE id = ? LIMIT 1");
        $stmt->bind_param('si', $status, $row['id']);
        $stmt->execute();
        $stmt->close();

        boreal_mark_fatura_pago($mysqli, (int) $row['fatura_mkauth'], (float) $row['valor']);
        boreal_log($mysqli, 'info', 'Fatura baixada via cron.', $row);
    }
}

echo "Cron Boreal finalizado.\n";
