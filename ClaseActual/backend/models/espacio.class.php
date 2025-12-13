<?php

require_once __DIR__ . '/../settings/client.php';

class Espacio{
    private $id;    
    private $nombre;
    private $descripcion;
    private $capacidad;
    private $precio_diario;
    private $tipo_espacio;
    private $politicas_reserva;
    private $activo;
    private $nombreTabla = "espacios"; 

    private $conexion;

    public function __construct(){
        $database = new db();
        $this -> conexion = $database->connection();
    }

    public function __destruct(){
        $this -> conexion = null;
    }

    // Método para crear un nuevo espacio
    public function create_espacio($nombre, $descripcion, $capacidad, $precio_diario, $tipo_espacio, $politicas_reserva){
        try{
            $sql = "INSERT INTO $this->nombreTabla (nombre, descripcion, capacidad, precio_diario, tipo_espacio, politicas_reserva) VALUES(:nombre, :descripcion, :capacidad, :precio_diario, :tipo_espacio, :politicas_reserva)";
            $prep = $this->conexion->prepare($sql);

            $prep->bindParam(':nombre', $nombre);
            $prep->bindParam(':descripcion', $descripcion);
            $prep->bindParam(':capacidad', $capacidad);
            $prep->bindParam(':precio_diario', $precio_diario);
            $prep->bindParam(':tipo_espacio', $tipo_espacio);
            $prep->bindParam(':politicas_reserva', $politicas_reserva);

            return $prep->execute();

        }catch(PDOException $e){
            die('Error al crear espacio: '. $e->getMessage());
        }
    }

    // Método para obtener todos los espacios
    public function get_all_espacios(){
        try {
            $sql = "SELECT id, nombre, descripcion, capacidad, precio_diario, tipo_espacio, politicas_reserva, activo, fecha_creacion, fecha_actualizacion FROM $this->nombreTabla";
            $result = $this->conexion->query($sql);
            
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al obtener espacios: " . $e->getMessage());
        }
    }

    // Método para obtener un espacio por su ID
    public function get_espacio_by_id($id){
        try{
            $sql = "SELECT id, nombre, descripcion, capacidad, precio_diario, tipo_espacio, politicas_reserva, activo, fecha_creacion, fecha_actualizacion FROM $this->nombreTabla WHERE id = :id";
            $prepare = $this->conexion->prepare($sql);
            $prepare->bindParam(':id', $id);
            $prepare->execute();

            return $prepare->fetch(PDO::FETCH_ASSOC);
        }catch (PDOException $e) {
            die("Error al obtener espacio: " . $e->getMessage());
        }
    }

    // Método para actualizar un espacio existente
    public function update_espacio($id, $nombre, $descripcion, $capacidad, $precio_diario, $tipo_espacio, $politicas_reserva, $activo){
        try {
            $sql = "UPDATE $this->nombreTabla 
                    SET nombre = :nombre, 
                        descripcion = :descripcion, 
                        capacidad = :capacidad, 
                        precio_diario = :precio_diario, 
                        tipo_espacio = :tipo_espacio, 
                        politicas_reserva = :politicas_reserva,
                        activo = :activo
                    WHERE id = :id";
            $prepare = $this->conexion->prepare($sql);

            $prepare->bindParam(':id', $id);
            $prepare->bindParam(':nombre', $nombre);
            $prepare->bindParam(':descripcion', $descripcion);
            $prepare->bindParam(':capacidad', $capacidad);
            $prepare->bindParam(':precio_diario', $precio_diario);
            $prepare->bindParam(':tipo_espacio', $tipo_espacio);
            $prepare->bindParam(':politicas_reserva', $politicas_reserva);
            $prepare->bindParam(':activo', $activo, PDO::PARAM_BOOL);

            return $prepare->execute();

        } catch (PDOException $e) {
            die("Error al actualizar espacio: " . $e->getMessage());
        }
    }

    // Método para eliminar un espacio por ID
    public function delete_espacio($id){
        try {
            $sql = "DELETE FROM $this->nombreTabla WHERE id = :id";
            $prepare = $this->conexion->prepare($sql);
            $prepare->bindParam(':id', $id);

            return $prepare->execute();
            
        } catch (PDOException $e) {
            die("Error al eliminar espacio: " . $e->getMessage());
        }
    }

    // Obtiene el número total de espacios
    public function get_total_spaces(){
        try {
            $sql = "SELECT COUNT(id) FROM $this->nombreTabla";
            $result = $this->conexion->query($sql);
            return $result->fetchColumn();
        } catch (PDOException $e) {
            die("Error al obtener el total de espacios: " . $e->getMessage());
        }
    }

    // Obtiene el número total de espacios activos
    public function get_total_active_spaces(){
        try {
            $sql = "SELECT COUNT(id) FROM $this->nombreTabla WHERE activo = TRUE";
            $result = $this->conexion->query($sql);
            return $result->fetchColumn();
        } catch (PDOException $e) {
            die("Error al obtener el total de espacios activos: " . $e->getMessage());
        }
    }
}
