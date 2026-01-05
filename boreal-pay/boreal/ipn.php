<?php
define('BOREAL_PUBLIC', true);
require_once dirname(__FILE__) . '/init.php';

$payload = file_get_contents('php://input');
$data = json_decode($payload, true);
if (!$data) {
    boreal_log($mysqli, 'erro', 'Payload IPN invalido.', $payload);
    boreal_json_response(array('sucesso' => false, 'erro' => 'Payload invalido.'), 400);
}

$config = boreal_get_config($mysqli);
$secret = isset($_SERVER['HTTP_WEBHOOK_SECRET']) ? $_SERVER['HTTP_WEBHOOK_SECRET'] : '';
if (!empty($config['client_secret']) && $secret !== $config['client_secret']) {
    boreal_log($mysqli, 'erro', 'Assinatura IPN invalida.', array('secret' => $secret));
    boreal_json_response(array('sucesso' => false, 'erro' => 'Assinatura invalida.'), 401);
}

$txid = null;
if (isset($data['Id'])) {
    $txid = $data['Id'];
} elseif (isset($data['id'])) {
    $txid = $data['id'];
} elseif (isset($data['TransactionId'])) {
    $txid = $data['TransactionId'];
}

$status = null;
if (isset($data['Status'])) {
    $status = $data['Status'];
} elseif (isset($data['status'])) {
    $status = $data['status'];
}

$valor_pago = 0;
if (isset($data['PaidValue'])) {
    $valor_pago = (float) $data['PaidValue'];
} elseif (isset($data['amount'])) {
    $valor_pago = ((float) $data['amount']) / 100;
}

if (!$txid) {
    boreal_log($mysqli, 'erro', 'IPN sem TXID.', $data);
    boreal_json_response(array('sucesso' => false, 'erro' => 'TXID ausente.'), 400);
}

$sql = "SELECT * FROM boreal_faturas WHERE txid = ? LIMIT 1";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('s', $txid);
$stmt->execute();
$result = $stmt->get_result();
$fatura = $result->fetch_assoc();
$stmt->close();

if (!$fatura) {
    boreal_log($mysqli, 'erro', 'Fatura Boreal nao encontrada.', $data);
    boreal_json_response(array('sucesso' => false, 'erro' => 'Fatura nao encontrada.'), 404);
}

$novo_status = strtolower($status);
$update_sql = "UPDATE boreal_faturas SET status = ? WHERE id = ? LIMIT 1";
$update_stmt = $mysqli->prepare($update_sql);
$update_stmt->bind_param('si', $novo_status, $fatura['id']);
$update_stmt->execute();
$update_stmt->close();

if ($novo_status === 'paid' || $novo_status === 'pago') {
    boreal_mark_fatura_pago($mysqli, (int) $fatura['fatura_mkauth'], $valor_pago > 0 ? $valor_pago : $fatura['valor']);
}

boreal_log($mysqli, 'info', 'IPN processado.', $data);
boreal_json_response(array('sucesso' => true));
