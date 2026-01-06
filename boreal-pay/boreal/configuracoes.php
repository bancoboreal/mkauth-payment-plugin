<?php
require_once dirname(__FILE__) . '/init.php';

$config = boreal_get_config($mysqli);
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = isset($_POST['client_id']) ? trim($_POST['client_id']) : '';
    $client_secret = isset($_POST['client_secret']) ? trim($_POST['client_secret']) : '';
    $token = isset($_POST['token']) ? trim($_POST['token']) : '';
    $ambiente = isset($_POST['ambiente']) ? trim($_POST['ambiente']) : 'sandbox';
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    $baixa_automatica = isset($_POST['baixa_automatica']) ? 1 : 0;

    $sql = "UPDATE boreal_config SET client_id = ?, client_secret = ?, token = ?, ambiente = ?, ativo = ?, baixa_automatica = ? WHERE id = 1";
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('ssssii', $client_id, $client_secret, $token, $ambiente, $ativo, $baixa_automatica);
        $stmt->execute();
        $stmt->close();
        $mensagem = 'Configura\u00e7\u00f5es salvas com sucesso.';
        $config = boreal_get_config($mysqli);
    } else {
        $mensagem = 'Erro ao salvar configura\u00e7\u00f5es.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR" class="has-navbar-fixed-top">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MK-AUTH :: Boreal Pay - Configura\u00e7\u00f5es</title>
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
        <li class="is-active"><a href="#" aria-current="page">BOREAL PAY - CONFIGURA\u00c7\u00d5ES</a></li>
    </ul>
</nav>

<section class="section">
    <div class="container">
        <div class="box">
            <h2 class="title is-4">Configura\u00e7\u00f5es Boreal Pay</h2>

            <?php if ($mensagem): ?>
                <div class="notification is-info"><?php echo htmlspecialchars($mensagem); ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="field">
                    <label class="label">Client ID (Workspace)</label>
                    <div class="control">
                        <input class="input" type="text" name="client_id" value="<?php echo htmlspecialchars($config['client_id']); ?>">
                    </div>
                </div>

                <div class="field">
                    <label class="label">Client Secret (Webhook)</label>
                    <div class="control">
                        <input class="input" type="text" name="client_secret" value="<?php echo htmlspecialchars($config['client_secret']); ?>">
                    </div>
                </div>

                <div class="field">
                    <label class="label">Token (Bearer)</label>
                    <div class="control">
                        <input class="input" type="text" name="token" value="<?php echo htmlspecialchars($config['token']); ?>">
                    </div>
                </div>

                <div class="field">
                    <label class="label">Ambiente</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="ambiente">
                                <option value="sandbox" <?php echo $config['ambiente'] === 'sandbox' ? 'selected' : ''; ?>>Sandbox</option>
                                <option value="producao" <?php echo $config['ambiente'] === 'producao' ? 'selected' : ''; ?>>Produ\u00e7\u00e3o</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="checkbox">
                        <input type="checkbox" name="ativo" <?php echo (int) $config['ativo'] === 1 ? 'checked' : ''; ?>>
                        Ativar integra\u00e7\u00e3o
                    </label>
                </div>

                <div class="field">
                    <label class="checkbox">
                        <input type="checkbox" name="baixa_automatica" <?php echo (int) $config['baixa_automatica'] === 1 ? 'checked' : ''; ?>>
                        Baixa autom\u00e1tica (cron)
                    </label>
                </div>

                <div class="field">
                    <div class="control">
                        <button class="button is-primary" type="submit">Salvar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include('../../baixo.php'); ?>

<script src="../../menu.js.hhvm"></script>
</body>
</html>
