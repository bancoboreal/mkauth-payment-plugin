<?php
// Configuração de erros (útil para debug, igual ao exemplo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. CARREGA O CORE DO MK-AUTH
// Isso define as constantes de banco (CONHOSTNAME, etc) e caminhos corretos
if (file_exists('/opt/mk-auth/include/addons.inc.hhvm')) {
    include('/opt/mk-auth/include/addons.inc.hhvm');
} else {
    // Fallback de segurança se o arquivo não existir
    if (!file_exists(dirname(__FILE__) . '/addons.class.php')) {
        die('Aguarde o processamento do MK-Auth, acesse novamente em alguns instantes!');
    }
    // Tenta carregar uma classe local se o core falhar (comum em addons)
    @include(dirname(__FILE__) . '/addons.class.php');
}

// 2. CONFIGURAÇÃO DA SESSÃO
// Define o nome 'mka' para compartilhar o login do admin
if (session_status() === PHP_SESSION_NONE) {
    session_name('mka');
    if (!isset($_SESSION)) session_start();
}

// 3. VERIFICAÇÃO DE SEGURANÇA
// Se a página não tiver a constante BOREAL_PUBLIC definida (ex: visualizar.php), exige login
if (!defined('BOREAL_PUBLIC')) {
    // Verifica requisições AJAX
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        if (!isset($_SESSION['MKA_Logado'])) {
            die(json_encode(array('erro' => true, 'log' => 'Acesso negado, entre novamente em sua conta mkauth!')));
        }
    } else {
        // Acesso direto via navegador
        if (!isset($_SESSION['MKA_Logado'])) {
            exit('Acesso negado... <a href="/admin/">Fazer Login</a>');
        }
    }
}

// 4. CONEXÃO COM BANCO DE DADOS
// Usa as constantes carregadas pelo passo 1
$mysqli = new mysqli(CONHOSTNAME, CONUSERNAME, CONPASSWRD, CONDATABASE);
if ($mysqli->connect_errno) {
    echo "Falha ao conectar: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    exit;
}
$mysqli->set_charset('utf8');

// 5. CARREGA BIBLIOTECAS DO BOREAL PAY
require_once dirname(__FILE__) . '/funcoes.php';
require_once dirname(__FILE__) . '/class.bank.php';

?>