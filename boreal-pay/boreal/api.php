<?php
require_once dirname(__FILE__) . '/init.php';
require_once dirname(__FILE__) . '/bancos/boreal.php';

$acao = isset($_GET['action']) ? $_GET['action'] : '';

if ($acao === 'gerar') {
    $titulo = isset($_GET['titulo']) ? (int) $_GET['titulo'] : 0;
    $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'pix';
    $redirect = isset($_GET['redirect']) ? (int) $_GET['redirect'] : 0;

    if ($titulo <= 0) {
        boreal_json_response(array('sucesso' => false, 'erro' => 'Titulo invalido.'), 400);
    }

    $sql = "SELECT * FROM vtab_titulos WHERE titulo = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        boreal_json_response(array('sucesso' => false, 'erro' => 'Erro ao preparar consulta.'), 500);
    }

    $stmt->bind_param('i', $titulo);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fatura = $resultado->fetch_assoc();
    $stmt->close();

    if (!$fatura) {
        boreal_json_response(array('sucesso' => false, 'erro' => 'Fatura nao encontrada.'), 404);
    }

    $dados_fatura = array(
        'id' => (int) $fatura['titulo'],
        'valor' => (float) $fatura['valor'],
        'vencimento' => $fatura['datavenc'],
        'expiracao' => $fatura['datavenc'],
        'cliente_nome' => $fatura['nome'],
        'cliente_documento' => $fatura['cpf'],
        'cliente_rua' => $fatura['rua'],
        'cliente_numero' => $fatura['numero'],
        'cliente_bairro' => $fatura['bairro'],
        'cliente_cidade' => $fatura['cidade'],
        'cliente_uf' => $fatura['uf'],
        'cliente_cep' => $fatura['cep'],
        'tipo' => $tipo,
    );

    $config = boreal_get_config($mysqli);
    $banco = new BorealBank($config, $mysqli);
    $resultado = $banco->gerar_pagamento($dados_fatura);

    if (!$resultado['sucesso']) {
        boreal_json_response(array('sucesso' => false, 'erro' => $resultado['erro']), 502);
    }

    if ($redirect === 1 && $tipo === 'boleto') {
        $dados_formatados = isset($resultado['dados_formatados']) ? $resultado['dados_formatados'] : array();
        if (!empty($dados_formatados['pdf_url'])) {
            header('Location: ' . $dados_formatados['pdf_url']);
            exit;
        }
    }

    boreal_log($mysqli, 'info', 'Pagamento gerado com sucesso.', $resultado);
    boreal_json_response(array('sucesso' => true, 'dados' => $resultado));
}

boreal_json_response(array('sucesso' => false, 'erro' => 'Acao invalida.'), 400);
