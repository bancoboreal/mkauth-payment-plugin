<?php
function boreal_escape($mysqli, $value)
{
    return $mysqli->real_escape_string($value);
}

function boreal_log($mysqli, $tipo, $mensagem, $payload = null)
{
    $sql = "INSERT INTO boreal_logs (tipo, mensagem, payload, data_criacao) VALUES (?, ?, ?, NOW())";
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $payload_text = $payload !== null ? json_encode($payload) : null;
        $stmt->bind_param('sss', $tipo, $mensagem, $payload_text);
        $stmt->execute();
        $stmt->close();
    }
}

function boreal_get_config($mysqli)
{
    $result = $mysqli->query('SELECT * FROM boreal_config ORDER BY id DESC LIMIT 1');
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return array(
        'client_id' => '',
        'client_secret' => '',
        'token' => '',
        'ambiente' => 'sandbox',
        'ativo' => 0,
        'baixa_automatica' => 0,
    );
}

function boreal_base_url($ambiente)
{
    if ($ambiente === 'producao') {
        return 'https://api.bancoboreal.com.br';
    }
    return 'https://api.development.bancoboreal.com.br';
}

function boreal_format_iso($date)
{
    if (!$date) {
        return null;
    }
    $timestamp = strtotime($date);
    if (!$timestamp) {
        return null;
    }
    return gmdate('Y-m-d\TH:i:s\Z', $timestamp);
}

function boreal_mark_fatura_pago($mysqli, $fatura_id, $valor_pago)
{
    $recibo = substr(md5((int) $fatura_id), -8);
    $sql = "UPDATE sis_lanc SET formapag = 'boreal', `status` = 'pago', num_recibos = 1, datapag = NOW(), coletor = 'boreal', valorpag = ?, tarifa_paga = '0', valordesc = '0', recibo = ? WHERE id = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('dsi', $valor_pago, $recibo, $fatura_id);
        $stmt->execute();
        $stmt->close();
    }
}

function boreal_json_response($data, $status = 200)
{
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}
