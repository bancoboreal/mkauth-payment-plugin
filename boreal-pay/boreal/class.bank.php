<?php
abstract class BorealBankBase
{
    protected $config;
    protected $mysqli;

    public function __construct($config, $mysqli)
    {
        $this->config = $config;
        $this->mysqli = $mysqli;
    }

    abstract public function gerar_pagamento($dados_fatura);

    abstract public function baixa($txid);
}
