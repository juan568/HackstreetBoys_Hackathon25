<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADMIN</title>
    <link rel="stylesheet" href="./../../assets/css/master.css">
</head>
<body class='fondo'>
    <div class="login">
    <h2>Hackstreet Flights</h2>
    <?php 
    error_reporting(0);
    if($_GET['mensaje']==144){
        echo "<p class='alertas'>Datos incorrectos. Intenta de nuevo.</p>";
    }elseif($_GET['mensaje']==812){
        echo "<p class='alertas'>La sesión se ha cerrado correctamente.</p>";
    }
    ?>
    <form action="./comprobar.php" method="post">
        <input type="text" name="usuario" placeholder="Coloca tu usuario">
        <input type="password" name="contrasena" placeholder="Coloca tu contrasena">
        <input type="submit" value="Entrar">
    </form>
    </div>
</body>
</html>