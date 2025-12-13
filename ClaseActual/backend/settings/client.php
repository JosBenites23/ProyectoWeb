<?php

//creamos clase db para poder llamar a la funcion en cualquier parte del codigo
class db{

    //funcion connection para establecer conexion y configuracion de errores y respuesta
        public function connection(){
            global $host, $port, $dbname, $user, $password;
        
            //nombre fuente de datos conexion con la base de datos
            $Url_conexion = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    
        try{
            //creamos la instancia de PDO(php data object)
            $conexion_bd = new PDO($Url_conexion, $user, $password);
    
            //configuracion de atributos PDO
            //configuracion manejo de errores
            $conexion_bd -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
            //configuracion para manejar json, array asociativo
            $conexion_bd -> setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
            return $conexion_bd;
        }catch(PDOException $e){
            die('Error de conexion: '. $e->getMessage());
        }
    }
}