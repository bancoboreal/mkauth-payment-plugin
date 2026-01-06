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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MK-AUTH :: Boreal Pay - Pagamento</title>
    <link href="../../estilos/mk-auth.css" rel="stylesheet" type="text/css" />
    <link href="../../estilos/font-awesome.css" rel="stylesheet" type="text/css" />
    <link href="../../estilos/bi-icons.css" rel="stylesheet" type="text/css" />
    <script src="../../scripts/jquery.js"></script>
    <script src="../../scripts/mk-auth.js"></script>
</head>
<body>
<section class="section">
    <div class="container is-max-desktop">
        <div class="box has-text-centered">
            <h2 class="title is-4">Pagamento via Pix</h2>
            <p class="subtitle is-6">Fatura MK Auth: <?php echo htmlspecialchars($fatura['titulo']); ?></p>
            <p class="is-size-5 has-text-weight-bold">Valor: R$ <?php echo number_format((float) $fatura['valor'], 2, ',', '.'); ?></p>
            <?php if ($qr_image): ?>
                <figure class="image is-128x128 is-inline-block mt-4">
                    <img src="<?php echo htmlspecialchars($qr_image); ?>" alt="QR Code Pix">
                </figure>
            <?php endif; ?>
            <div class="mt-4">
                <p class="has-text-weight-semibold">Copia e Cola</p>
                <div class="notification is-light" id="pix-code"><?php echo htmlspecialchars($pix); ?></div>
                <button class="button is-primary" type="button" onclick="copiarPix()">Copiar</button>
            </div>
        </div>
    </div>
</section>
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
