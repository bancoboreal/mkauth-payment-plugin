<?php
require_once dirname(__FILE__) . '/init.php';

$result = $mysqli->query('SELECT * FROM boreal_faturas ORDER BY data_criacao DESC LIMIT 200');
$faturas = array();
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $faturas[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR" class="has-navbar-fixed-top">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MK-AUTH :: Boreal Pay - Faturas</title>
    <link href="../../estilos/mk-auth.css" rel="stylesheet" type="text/css" />
    <link href="../../estilos/font-awesome.css" rel="stylesheet" type="text/css" />
    <link href="../../estilos/bi-icons.css" rel="stylesheet" type="text/css" />
    <script src="../../scripts/jquery.js"></script>
    <script src="../../scripts/mk-auth.js"></script>
</head>
<body>
<?php include('../../topo.php'); ?>

<nav class="breadcrumb has-bullet-separator is-centered" aria-label="breadcrumbs">
    <ul>
        <li><a href="#">ADDON</a></li>
        <li class="is-active"><a href="#" aria-current="page">BOREAL PAY - FATURAS</a></li>
    </ul>
</nav>

<section class="section">
    <div class="container">
        <div class="box">
            <h2 class="title is-4">Faturas Boreal</h2>
            <div class="table-container">
                <table class="table is-striped is-fullwidth">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fatura MK Auth</th>
                            <th>TXID</th>
                            <th>Status</th>
                            <th>Valor</th>
                            <th>Pix</th>
                            <th>Boleto</th>
                            <th>Criado em</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($faturas as $fatura): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($fatura['id']); ?></td>
                            <td><?php echo htmlspecialchars($fatura['fatura_mkauth']); ?></td>
                            <td><?php echo htmlspecialchars($fatura['txid']); ?></td>
                            <td><span class="tag is-info"><?php echo htmlspecialchars($fatura['status']); ?></span></td>
                            <td>R$ <?php echo number_format((float) $fatura['valor'], 2, ',', '.'); ?></td>
                            <td><?php echo $fatura['pix_copia_cola'] ? 'Sim' : 'N\u00e3o'; ?></td>
                            <td><?php if ($fatura['pdf_url']): ?><a href="<?php echo htmlspecialchars($fatura['pdf_url']); ?>" target="_blank">Abrir</a><?php else: ?>-<?php endif; ?></td>
                            <td><?php echo htmlspecialchars($fatura['data_criacao']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php include('../../baixo.php'); ?>

<script src="../../menu.js.hhvm"></script>
</body>
</html>
