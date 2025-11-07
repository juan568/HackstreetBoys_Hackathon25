<?php
require_once '../includes/config.php';
    
$i = new Conexion();
$q = $i->pdo->query("SELECT titulo FROM titulo ORDER BY ID DESC LIMIT 1");
$fila = $q->fetch(PDO::FETCH_ASSOC);
$hola = $fila['titulo'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mariscos Doña Pelos</title>
</head>
<body>
    <head>
        <h1><?php echo $hola; ?></h1>
        <link rel="stylesheet" href="./../assets/css/master.css">
        <p class="slogan">La comida mas limpia</p>

        <nav>
            <ul>
                <li><a href="./">Inicio</a></li>
                <li><a href="./catalogo.php">Catalogo</a></li>
                <li><a href="./sobrenosotros.php">Sobre Nosotros</a></li>
                <li><a href="./contactanos.php">Contactanos</a></li>
            </ul>
        </nav>
    </head>
    <section>