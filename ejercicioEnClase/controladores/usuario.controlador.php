<?php

include_once("../modelos/usuario.class.php");

class UsuarioControlador{
    private $ObjUsuario;

    public function __construct(){
        $this->ObjUsuario = new Usuario();
        $this->ObjUsuario->SetRutaArchivo("../public/");
    }

    public function SaveValores($cedula, $nombre, $email, $imagen){
        $this->ObjUsuario->SetValores($cedula, $nombre, $email, $imagen);
        $this->ObjUsuario->SaveValores();
    }

    public function setRutaArchivo($rutaArchivo){
        $this->ObjUsuario->SetRutaArchivo($rutaArchivo);
    }

    public function GetValores(){
        header("Content-Type: application/json");
        $registros = $this->ObjUsuario->GetValores();
        echo json_encode($registros);
    }
}

$ObjUsuarioControlador = new UsuarioControlador();
if(isset($_POST['opcion'])){
    $nombre_arreglo = explode(".", $_FILES['imagen']['name']);
    $nombre_archivo = uniqid() . "." . $nombre_arreglo[count($nombre_arreglo) - 1];
    move_uploaded_file($_FILES['imagen']['tmp_name'], "../public/" . $nombre_archivo);
    $ObjUsuarioControlador->setRutaArchivo("../public/datos/");
    $ObjUsuarioControlador->SaveValores($_POST['cedula'], $_POST['nombre'], $_POST['email'], $nombre_archivo);
    header("Location: ../index.html");
}else{
    $ObjUsuarioControlador->GetValores();
}

?>