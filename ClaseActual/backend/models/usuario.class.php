<?php

include_once ("../settings/bd.php" );

class Usuario {
    private $id;    
    private $nombre;
    private $correo;
    private $cedula;
    private $telefono;
    private $rol;
    private $estado;  
    private $nombreTabla = "usuarios"; 

    private $conexion;
    
    public function __construct() {
        global $host, $user, $password, $port, $dbname;
        try {
            $this->conexion = new mysqli($host, $user, $password, $dbname);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function __destruct() {
        $this->conexion->close();
    }

    public function insertarUsuario($nombre, $correo, $cedula, $telefono, $rol){
        try {
            $sql = "INSERT INTO $this->nombreTabla (nombre, correo, cedula, telefono, rol) VALUES ('$nombre', '$correo', '$cedula', '$telefono', '$rol')";
            $this->conexion->query($sql);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function getUsuarios(){
        try {
            $sql = "SELECT * FROM $this->nombreTabla";
            $result = $this->conexion->query($sql);
            $usuarios = [];
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    $usuarios[] = $row;
                }
            }
            return $usuarios;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}

$objetoUsuario = new Usuario();
$objetoUsuario->insertarUsuario("Juan", "juan@gmail.com", "12345678", "12345678", "admin");
$usuarios = $objetoUsuario->getUsuarios();
var_dump($usuarios);

?>