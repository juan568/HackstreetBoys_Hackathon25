<?php
// flights_dashboard.php
// Este script PHP lee el archivo JSON generado por Python y muestra los datos.

$json_file = __DIR__ . '/cache/dashboard_data.json';
$data = null;

if (file_exists($json_file)) {
    $json_content = file_get_contents($json_file);
    $data = json_decode($json_content, true);
}

$flights = $data['flights'] ?? [];
$status = $data['status'] ?? [
    'api_status' => 'INACTIVO',
    'last_cleanup' => 'N/D',
    'alert' => 'El script de Python no ha generado el archivo JSON.'
];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard de Vuelos (OpenSky Data)</title>
    <link rel="stylesheet" href="./../../assets/css/master.css">
</head>
<body>
    <div class="container">
        <h2>✈ Vuelos en el Área Filtrada (CDMX)</h2>
        
        <div class="status-box <?php echo (strpos($status['api_status'], 'ERROR') !== false) ? 'status-error' : ''; ?>">
            <p><strong>Vuelos Activos:</strong> <?php echo count($flights); ?></p>
            <p><strong>Estado API:</strong> <?php echo $status['api_status']; ?></p>
            <p><strong>Última Actualización:</strong> <?php echo $status['last_cleanup']; ?></p>
            <?php if (!empty($status['alert'])): ?>
                <p><strong>Alerta:</strong> <?php echo $status['alert']; ?></p>
            <?php endif; ?>
        </div>

        <h3>Detalles de Vuelos</h3>
        <table>
            <thead>
                <tr>
                    <th>Callsign</th>
                    <th>ICAO24</th>
                    <th>Lat/Lon</th>
                    <th>Altitud (m)</th>
                    <th>Heading (°)</th>
                    <th>En Tierra</th>
                    <th>Partida Est.</th>
                    <th>Llegada Est.</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($flights as $flight): ?>
                <tr>
                    <td><strong><?php echo $flight['callsign']; ?></strong></td>
                    <td><?php echo $flight['icao24']; ?></td>
                    <td><?php echo round($flight['lat'], 2) . ' / ' . round($flight['lon'], 2); ?></td>
                    <td><?php echo number_format($flight['altitude']); ?></td>
                    <td><?php echo $flight['heading']; ?></td>
                    <td><?php echo $flight['on_ground'] ? 'Sí' : 'No'; ?></td>
                    <td><?php echo $flight['dep_icao']; ?></td>
                    <td><?php echo $flight['arr_icao']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</body>
</html>