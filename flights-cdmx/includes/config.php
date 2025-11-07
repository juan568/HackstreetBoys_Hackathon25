<?php
    //$conexion = new mysqli("localhost", "root", "", "");

    class Conexion{
        private $host = "localhost";
        private $userdb = "root";
        private $passdb = "";
        private $namedb = "7s22";
        private $charset = "utf8mb4"; //las privadas solo dentro de la clase

        public $pdo; //las publicas dentro de la db

        public function __construct()
        {
            try{
                $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->namedb . ";charset=" . $this->charset;
                $this->pdo = new PDO($dsn, $this->userdb, $this->passdb);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }catch(PDOException $e){
                echo "Error en la Conexión" . $e->getMessage();
            }
        }
    }
?>