<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BOREAL_PUBLIC')) {
    if (!isset($_SESSION['MKA_Logado']) || $_SESSION['MKA_Logado'] != 1) {
        header('HTTP/1.1 403 Forbidden');
        echo 'Acesso negado.';
        exit;
    }
}

$possible_configs = array(
    '/opt/mk-auth/admin/config.php',
    '/opt/mk-auth/admin/config.inc.php',
);
foreach ($possible_configs as $config_path) {
    if (file_exists($config_path)) {
        require_once $config_path;
        break;
    }
}

if (!defined('CONHOSTNAME')) {
    define('CONHOSTNAME', 'localhost');
}
if (!defined('CONUSERNAME')) {
    define('CONUSERNAME', 'root');
}
if (!defined('CONPASSWORD')) {
    define('CONPASSWORD', '');
}
if (!defined('CONDB')) {
    define('CONDB', 'mkradius');
}

$mysqli = new mysqli(CONHOSTNAME, CONUSERNAME, CONPASSWORD, CONDB);
if ($mysqli->connect_error) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Erro ao conectar no banco de dados.';
    exit;
}
$mysqli->set_charset('utf8');

require_once dirname(__FILE__) . '/funcoes.php';
require_once dirname(__FILE__) . '/class.bank.php';
