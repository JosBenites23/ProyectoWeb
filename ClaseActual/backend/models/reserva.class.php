<?php

require_once __DIR__ . '/../settings/client.php';

class Reserva{
    private $id;    
    private $id_usuario;
    private $id_espacio;
    private $fecha_inicio;
    private $fecha_fin;
    private $estado_reserva;
    private $codigo_qr;
    private $total_pagado;
    private $nombreTabla = "reservas"; 

    private $conexion;

    public function __construct(){
        $database = new db();
        $this -> conexion = $database->connection();
    }

    public function __destruct(){
        $this -> conexion = null;
    }

    // Método para crear una nueva reserva
    public function create_reserva($id_usuario, $id_espacio, $fecha_inicio, $fecha_fin, $total_pagado, $codigo_qr = null, $estado_reserva = 'pendiente'){
        try{
            $sql = "INSERT INTO $this->nombreTabla (id_usuario, id_espacio, fecha_inicio, fecha_fin, estado_reserva, codigo_qr, total_pagado) VALUES(:id_usuario, :id_espacio, :fecha_inicio, :fecha_fin, :estado_reserva, :codigo_qr, :total_pagado)";
            $prep = $this->conexion->prepare($sql);

            $prep->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $prep->bindParam(':id_espacio', $id_espacio, PDO::PARAM_INT);
            $prep->bindParam(':fecha_inicio', $fecha_inicio);
            $prep->bindParam(':fecha_fin', $fecha_fin);
            $prep->bindParam(':estado_reserva', $estado_reserva);
            $prep->bindParam(':codigo_qr', $codigo_qr);
            $prep->bindParam(':total_pagado', $total_pagado);

            return $prep->execute();

        }catch(PDOException $e){
            die('Error al crear reserva: '. $e->getMessage());
        }
    }

    // Método para obtener todas las reservas
    public function get_all_reservas(){
        try {
            $sql = "SELECT id, id_usuario, id_espacio, fecha_inicio, fecha_fin, estado_reserva, codigo_qr, total_pagado, fecha_reserva, fecha_actualizacion FROM $this->nombreTabla";
            $result = $this->conexion->query($sql);
            
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al obtener reservas: " . $e->getMessage());
        }
    }

    // Método para obtener una reserva por su ID
    public function get_reserva_by_id($id){
        try{
            $sql = "SELECT id, id_usuario, id_espacio, fecha_inicio, fecha_fin, estado_reserva, codigo_qr, total_pagado, fecha_reserva, fecha_actualizacion FROM $this->nombreTabla WHERE id = :id";
            $prepare = $this->conexion->prepare($sql);
            $prepare->bindParam(':id', $id, PDO::PARAM_INT);
            $prepare->execute();

            return $prepare->fetch(PDO::FETCH_ASSOC);
        }catch (PDOException $e) {
            die("Error al obtener reserva: " . $e->getMessage());
        }
    }

    // Método para obtener reservas por ID de usuario
    public function get_reservas_by_user_id($id_usuario){
        try{
            $sql = "SELECT id, id_usuario, id_espacio, fecha_inicio, fecha_fin, estado_reserva, codigo_qr, total_pagado, fecha_reserva, fecha_actualizacion FROM $this->nombreTabla WHERE id_usuario = :id_usuario ORDER BY fecha_inicio DESC";
            $prepare = $this->conexion->prepare($sql);
            $prepare->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $prepare->execute();

            return $prepare->fetchAll(PDO::FETCH_ASSOC);
        }catch (PDOException $e) {
            die("Error al obtener reservas por usuario: " . $e->getMessage());
        }
    }

    // Método para obtener reservas por ID de espacio
    public function get_reservas_by_espacio_id($id_espacio){
        try{
            $sql = "SELECT id, id_usuario, id_espacio, fecha_inicio, fecha_fin, estado_reserva, codigo_qr, total_pagado, fecha_reserva, fecha_actualizacion FROM $this->nombreTabla WHERE id_espacio = :id_espacio ORDER BY fecha_inicio DESC";
            $prepare = $this->conexion->prepare($sql);
            $prepare->bindParam(':id_espacio', $id_espacio, PDO::PARAM_INT);
            $prepare->execute();

            return $prepare->fetchAll(PDO::FETCH_ASSOC);
        }catch (PDOException $e) {
            die("Error al obtener reservas por espacio: " . $e->getMessage());
        }
    }
    
    // Método para actualizar una reserva existente
    public function update_reserva($id, $id_usuario, $id_espacio, $fecha_inicio, $fecha_fin, $estado_reserva, $codigo_qr, $total_pagado){
        try {
            $sql = "UPDATE $this->nombreTabla 
                    SET id_usuario = :id_usuario, 
                        id_espacio = :id_espacio, 
                        fecha_inicio = :fecha_inicio, 
                        fecha_fin = :fecha_fin, 
                        estado_reserva = :estado_reserva,
                        codigo_qr = :codigo_qr,
                        total_pagado = :total_pagado
                    WHERE id = :id";
            $prepare = $this->conexion->prepare($sql);

            $prepare->bindParam(':id', $id, PDO::PARAM_INT);
            $prepare->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $prepare->bindParam(':id_espacio', $id_espacio, PDO::PARAM_INT);
            $prepare->bindParam(':fecha_inicio', $fecha_inicio);
            $prepare->bindParam(':fecha_fin', $fecha_fin);
            $prepare->bindParam(':estado_reserva', $estado_reserva);
            $prepare->bindParam(':codigo_qr', $codigo_qr);
            $prepare->bindParam(':total_pagado', $total_pagado);

            return $prepare->execute();

        } catch (PDOException $e) {
            die("Error al actualizar reserva: " . $e->getMessage());
        }
    }

    // Método para eliminar una reserva por ID
    public function delete_reserva($id){
        try {
            $sql = "DELETE FROM $this->nombreTabla WHERE id = :id";
            $prepare = $this->conexion->prepare($sql);
            $prepare->bindParam(':id', $id, PDO::PARAM_INT);

            return $prepare->execute();
            
        } catch (PDOException $e) {
            die("Error al eliminar reserva: " . $e->getMessage());
        }
    }

    // Método para verificar disponibilidad de un espacio en un rango de fechas
    public function check_availability($id_espacio, $fecha_inicio, $fecha_fin){
        try {
            $sql = "SELECT COUNT(*) FROM $this->nombreTabla 
                    WHERE id_espacio = :id_espacio 
                    AND estado_reserva IN ('pendiente', 'confirmada') -- Considerar reservas pendientes y confirmadas
                    AND (
                        (fecha_inicio < :fecha_fin AND fecha_fin > :fecha_inicio) -- Existe solapamiento
                    )";
            $prepare = $this->conexion->prepare($sql);
            $prepare->bindParam(':id_espacio', $id_espacio, PDO::PARAM_INT);
            $prepare->bindParam(':fecha_inicio', $fecha_inicio);
            $prepare->bindParam(':fecha_fin', $fecha_fin);
            $prepare->execute();

            return $prepare->fetchColumn() == 0; // Retorna true si no hay reservas que se solapen
        } catch (PDOException $e) {
            die("Error al verificar disponibilidad: " . $e->getMessage());
        }
    }

    // Obtiene el número total de reservas
    public function get_total_reservations(){
        try {
            $sql = "SELECT COUNT(id) FROM $this->nombreTabla";
            $result = $this->conexion->query($sql);
            return $result->fetchColumn();
        } catch (PDOException $e) {
            die("Error al obtener el total de reservas: " . $e->getMessage());
        }
    }

    // Obtiene el número de reservas por estado
    public function get_reservations_count_by_status($estado_reserva){
        try {
            $sql = "SELECT COUNT(id) FROM $this->nombreTabla WHERE estado_reserva = :estado_reserva";
            $prepare = $this->conexion->prepare($sql);
            $prepare->bindParam(':estado_reserva', $estado_reserva);
            $prepare->execute();
            return $prepare->fetchColumn();
        } catch (PDOException $e) {
            die("Error al obtener el total de reservas por estado: " . $e->getMessage());
        }
    }

    // Obtiene los espacios más reservados (por conteo de reservas)
    public function get_most_reserved_spaces($limit = 5){
        try {
            $sql = "SELECT id_espacio, COUNT(id_espacio) as total_reservas 
                    FROM $this->nombreTabla 
                    GROUP BY id_espacio 
                    ORDER BY total_reservas DESC 
                    LIMIT :limit";
            $prepare = $this->conexion->prepare($sql);
            $prepare->bindParam(':limit', $limit, PDO::PARAM_INT);
            $prepare->execute();
            return $prepare->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al obtener los espacios más reservados: " . $e->getMessage());
        }
    }
}
