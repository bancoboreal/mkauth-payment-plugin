<?php
define('BOREAL_PUBLIC', true);
require_once dirname(__FILE__) . '/init.php';
require_once dirname(__FILE__) . '/bancos/boreal.php';

$titulo = isset($_GET['titulo']) ? (int) $_GET['titulo'] : 0;
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'pix';

if ($titulo <= 0) {
    echo 'Titulo invalido.';
    exit;
}

$sql = "SELECT * FROM vtab_titulos WHERE titulo = ? LIMIT 1";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $titulo);
$stmt->execute();
$resultado = $stmt->get_result();
$fatura = $resultado->fetch_assoc();
$stmt->close();

if (!$fatura) {
    echo 'Fatura nao encontrada.';
    exit;
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
    echo 'Erro ao gerar pagamento: ' . htmlspecialchars($resultado['erro']);
    exit;
}

$dados_formatados = isset($resultado['dados_formatados']) ? $resultado['dados_formatados'] : array();

if ($tipo === 'boleto' && !empty($dados_formatados['pdf_url'])) {
    header('Location: ' . $dados_formatados['pdf_url']);
    exit;
}

$pix = isset($dados_formatados['pix_copia_cola']) ? $dados_formatados['pix_copia_cola'] : '';
$qr_image = '';
if ($pix) {
    $possiveis = array(
        dirname(__FILE__) . '/lib/phpqrcode/qrlib.php',
        '/admin/scripts/phpqrcode/qrlib.php',
        '/opt/mk-auth/admin/scripts/phpqrcode/qrlib.php',
    );
    $qrlib = null;
    foreach ($possiveis as $arquivo) {
        if (file_exists($arquivo)) {
            $qrlib = $arquivo;
            break;
        }
    }
    if ($qrlib) {
        require_once $qrlib;
        ob_start();
        QRcode::png($pix, null, QR_ECLEVEL_L, 4);
        $png = ob_get_clean();
        if ($png) {
            $qr_image = 'data:image/png;base64,' . base64_encode($png);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Boreal Pay - Pagamento</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; text-align: center; }
        .card { display: inline-block; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .pix { margin-top: 20px; word-break: break-all; }
        button { padding: 10px 16px; margin-top: 12px; }
    </style>
</head>
<body>
<div class="card">
    <h2>Pagamento via Pix</h2>
    <p>Fatura MK Auth: <?php echo htmlspecialchars($fatura['titulo']); ?></p>
    <p>Valor: R$ <?php echo number_format((float) $fatura['valor'], 2, ',', '.'); ?></p>
    <?php if ($qr_image): ?>
        <img src="<?php echo htmlspecialchars($qr_image); ?>" alt="QR Code Pix">
    <?php endif; ?>
    <div class="pix">
        <strong>Copia e Cola</strong>
        <div id="pix-code"><?php echo htmlspecialchars($pix); ?></div>
        <button type="button" onclick="copiarPix()">Copiar</button>
    </div>
</div>
<script>
function copiarPix() {
    var texto = document.getElementById('pix-code').innerText;
    if (navigator.clipboard) {
        navigator.clipboard.writeText(texto).then(function() {
            alert('Pix copiado.');
        });
    }
}
</script>
</body>
</html>
