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
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Boreal Pay - Faturas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f5f5f5; }
        .tag { font-size: 12px; padding: 2px 6px; background: #eef; border-radius: 4px; }
    </style>
</head>
<body>
<h2>Faturas Boreal</h2>
<table>
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
            <td><span class="tag"><?php echo htmlspecialchars($fatura['status']); ?></span></td>
            <td>R$ <?php echo number_format((float) $fatura['valor'], 2, ',', '.'); ?></td>
            <td><?php echo $fatura['pix_copia_cola'] ? 'Sim' : 'N\u00e3o'; ?></td>
            <td><?php if ($fatura['pdf_url']): ?><a href="<?php echo htmlspecialchars($fatura['pdf_url']); ?>" target="_blank">Abrir</a><?php else: ?>-<?php endif; ?></td>
            <td><?php echo htmlspecialchars($fatura['data_criacao']); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</body>
</html>
