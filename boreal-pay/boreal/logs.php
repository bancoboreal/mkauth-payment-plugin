<?php
require_once dirname(__FILE__) . '/init.php';

$result = $mysqli->query('SELECT * FROM boreal_logs ORDER BY data_criacao DESC LIMIT 200');
$logs = array();
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR" class="has-navbar-fixed-top">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MK-AUTH :: Boreal Pay - Logs</title>
    <link href="../../../estilos/mk-auth.css" rel="stylesheet" type="text/css" />
    <link href="../../../estilos/font-awesome.css" rel="stylesheet" type="text/css" />
    <link href="../../../estilos/bi-icons.css" rel="stylesheet" type="text/css" />
    <script src="../../../scripts/jquery.js"></script>
    <script src="../../../scripts/mk-auth.js"></script>
</head>
<body>
<?php include('../../../topo.php'); ?>

<nav class="breadcrumb has-bullet-separator is-centered" aria-label="breadcrumbs">
    <ul>
        <li><a href="#">ADDON</a></li>
        <li class="is-active"><a href="#" aria-current="page">BOREAL PAY - LOGS</a></li>
    </ul>
</nav>

<section class="section">
    <div class="container">
        <div class="box">
            <h2 class="title is-4">Logs Boreal Pay</h2>
            <div class="table-container">
                <table class="table is-striped is-fullwidth">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Mensagem</th>
                            <th>Payload</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['data_criacao']); ?></td>
                            <td><?php echo htmlspecialchars($log['tipo']); ?></td>
                            <td><?php echo htmlspecialchars($log['mensagem']); ?></td>
                            <td><pre><?php echo htmlspecialchars($log['payload']); ?></pre></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php include('../../../baixo.php'); ?>

<script src="../../../menu.js.hhvm"></script>
</body>
</html>
