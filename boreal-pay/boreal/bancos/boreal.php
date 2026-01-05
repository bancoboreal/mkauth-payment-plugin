<?php
require_once dirname(__FILE__) . '/../funcoes.php';
require_once dirname(__FILE__) . '/../class.bank.php';

class BorealBank extends BorealBankBase
{
    private $base_url;

    public function __construct($config, $mysqli)
    {
        parent::__construct($config, $mysqli);
        $this->base_url = boreal_base_url($this->config['ambiente']);
    }

    public function gerar_pagamento($dados_fatura)
    {
        $tipo = isset($dados_fatura['tipo']) ? $dados_fatura['tipo'] : 'pix';
        $payload = $this->montar_payload($dados_fatura, $tipo);
        $endpoint = $tipo === 'boleto' ? '/api/boletos/' : '/api/pix-invoices/';

        $resposta = $this->request('POST', $endpoint, $payload);
        if (!isset($resposta['sucesso']) || !$resposta['sucesso']) {
            return $resposta;
        }

        $dados_api = $resposta['dados'];
        $txid = $this->extrair_valor($dados_api, array('id', 'transaction_id', 'TransactionId'));
        $status = $this->extrair_valor($dados_api, array('status', 'Status'));
        $pix_copia_cola = $this->extrair_valor($dados_api, array('pix', 'payment_info', 'QrCode', 'brcode'));
        $linha_digitavel = $this->extrair_valor($dados_api, array('BarCode', 'linha_digitavel', 'barcode'));
        $pdf_url = $this->extrair_valor($dados_api, array('Url', 'pdf_url'));

        $sql = "INSERT INTO boreal_faturas (fatura_mkauth, txid, status, valor, pix_copia_cola, linha_digitavel, pdf_url, data_criacao) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->mysqli->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('issdsss', $dados_fatura['id'], $txid, $status, $dados_fatura['valor'], $pix_copia_cola, $linha_digitavel, $pdf_url);
            $stmt->execute();
            $stmt->close();
        }

        $resposta['dados_formatados'] = array(
            'txid' => $txid,
            'status' => $status,
            'pix_copia_cola' => $pix_copia_cola,
            'linha_digitavel' => $linha_digitavel,
            'pdf_url' => $pdf_url,
        );

        return $resposta;
    }

    public function baixa($txid)
    {
        if (!$txid) {
            return array('sucesso' => false, 'erro' => 'TXID vazio.');
        }
        $endpoint = '/api/boletos/' . $txid;
        $resposta = $this->request('GET', $endpoint);
        if (!$resposta['sucesso']) {
            return $resposta;
        }
        $status = $this->extrair_valor($resposta['dados'], array('status', 'Status'));
        $resposta['status'] = $status;
        return $resposta;
    }

    private function montar_payload($dados_fatura, $tipo)
    {
        $amount = (int) round($dados_fatura['valor'] * 100);
        $payload = array(
            'amount' => $amount,
            'due_date' => boreal_format_iso($dados_fatura['vencimento']),
            'name' => $dados_fatura['cliente_nome'],
            'tax_id' => $dados_fatura['cliente_documento'],
            'street' => $dados_fatura['cliente_rua'],
            'street_number' => $dados_fatura['cliente_numero'],
            'neighborhood' => $dados_fatura['cliente_bairro'],
            'city' => $dados_fatura['cliente_cidade'],
            'state' => $dados_fatura['cliente_uf'],
            'postal_code' => $dados_fatura['cliente_cep'],
            'tags' => array('mkauth:' . $dados_fatura['id']),
        );

        if ($tipo === 'pix') {
            $payload['expiration_date'] = boreal_format_iso($dados_fatura['expiracao']);
        }

        return $payload;
    }

    private function request($method, $endpoint, $payload = null)
    {
        $url = rtrim($this->base_url, '/') . $endpoint;
        $headers = array('Content-Type: application/json');
        if (!empty($this->config['token'])) {
            $headers[] = 'Authorization: Bearer ' . $this->config['token'];
        }
        if (!empty($this->config['client_id'])) {
            $headers[] = 'X-Workspace-ID: ' . $this->config['client_id'];
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $response_body = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            boreal_log($this->mysqli, 'erro', 'Erro cURL Boreal: ' . $curl_error, $payload);
            return array('sucesso' => false, 'erro' => $curl_error);
        }

        $decoded = json_decode($response_body, true);
        if ($http_code < 200 || $http_code >= 300) {
            boreal_log($this->mysqli, 'erro', 'Erro API Boreal (' . $http_code . ')', array('payload' => $payload, 'response' => $response_body));
            return array('sucesso' => false, 'erro' => 'Erro API Boreal', 'http_code' => $http_code, 'dados' => $decoded);
        }

        return array('sucesso' => true, 'dados' => $decoded, 'http_code' => $http_code);
    }

    private function extrair_valor($dados, $chaves)
    {
        foreach ($chaves as $chave) {
            if (is_array($dados) && array_key_exists($chave, $dados)) {
                return $dados[$chave];
            }
        }
        return null;
    }
}
