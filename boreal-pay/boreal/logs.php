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
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Boreal Pay - Logs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f5f5f5; }
        pre { white-space: pre-wrap; }
    </style>
</head>
<body>
<h2>Logs Boreal Pay</h2>
<table>
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
</body>
</html>
