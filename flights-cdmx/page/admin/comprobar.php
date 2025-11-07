<?php
session_start();

$usu = $_POST['usuario'];
$con = $_POST['contrasena'];

include "./../../includes/config.php";

$i = new Conexion();
    $q = $i->pdo->query("SELECT * FROM usuarios WHERE usuario='$usu' AND contrasena='$con'");
    $contador = 0;
        while ($fila = $q->fetch(PDO::FETCH_ASSOC)){
            $_SESSION['idu'] = $fila['id'];
            $contador++;
        }
        if($contador> 0){
            header("location:./flights_dashboard.php");
        }else{
            header("location:./index.php?mensaje=144");
        }
?>