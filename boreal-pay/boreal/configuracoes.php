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
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Boreal Pay - Configura\u00e7\u00f5es</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        label { display: block; margin-top: 12px; font-weight: bold; }
        input, select { width: 100%; max-width: 420px; padding: 8px; }
        .alert { padding: 10px; background: #e8f4ff; border: 1px solid #b6d8ff; margin-bottom: 15px; }
        .row { margin-bottom: 8px; }
    </style>
</head>
<body>
<h2>Configura\u00e7\u00f5es Boreal Pay</h2>

<?php if ($mensagem): ?>
    <div class="alert"><?php echo htmlspecialchars($mensagem); ?></div>
<?php endif; ?>

<form method="post">
    <label>Client ID (Workspace)</label>
    <input type="text" name="client_id" value="<?php echo htmlspecialchars($config['client_id']); ?>">

    <label>Client Secret (Webhook)</label>
    <input type="text" name="client_secret" value="<?php echo htmlspecialchars($config['client_secret']); ?>">

    <label>Token (Bearer)</label>
    <input type="text" name="token" value="<?php echo htmlspecialchars($config['token']); ?>">

    <label>Ambiente</label>
    <select name="ambiente">
        <option value="sandbox" <?php echo $config['ambiente'] === 'sandbox' ? 'selected' : ''; ?>>Sandbox</option>
        <option value="producao" <?php echo $config['ambiente'] === 'producao' ? 'selected' : ''; ?>>Produ\u00e7\u00e3o</option>
    </select>

    <div class="row">
        <label><input type="checkbox" name="ativo" <?php echo (int) $config['ativo'] === 1 ? 'checked' : ''; ?>> Ativar integra\u00e7\u00e3o</label>
    </div>
    <div class="row">
        <label><input type="checkbox" name="baixa_automatica" <?php echo (int) $config['baixa_automatica'] === 1 ? 'checked' : ''; ?>> Baixa autom\u00e1tica (cron)</label>
    </div>

    <button type="submit">Salvar</button>
</form>
</body>
</html>
