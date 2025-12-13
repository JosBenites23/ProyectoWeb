<?php

//se usa variable estatica especial para evitar errores con las rutas y se trae la clase de conexion
require_once __DIR__ . '/../settings/client.php';

class Usuario{
    // Propiedades del modelo
    private $id;    
    private $nombre;
    private $correo;
    private $contrasena; // Nueva propiedad para la contraseña
    private $cedula;
    private $telefono;
    private $id_rol; // Cambiado de 'rol' a 'id_rol' para FK
    private $nombreTabla = "usuarios"; 

    // Propiedad para el objeto de conexión PDO
    private $conexion;

    public function __construct(){
        //se instancia la clase db
        $database = new db();
        //se obtiene la conexion PDO
        $this -> conexion = $database->connection();
    }

    public function __destruct(){
        //no hace falta cerrar la conexion ya que PDO lo hace automaticamente cuando termina el script pero por buena practica anulamos el objeto para liberar recursos
        $this -> conexion = null;
    }

    //insersion de usuario de forma segura usando sentencias preparadas
    public function create_user($nombre, $correo, $contrasena, $cedula, $telefono, $id_rol){
        try{
            $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT); // Hash the contrasena
            //preparar la consulta
            $sql = "INSERT INTO $this->nombreTabla (nombre, correo, contrasena, cedula, telefono, id_rol) VALUES(:nombre, :correo, :contrasena, :cedula, :telefono, :id_rol)";

            //crear una sentencia preparada
            $prep = $this->conexion->prepare($sql);

            //vincular los parametros recibidos con la sentencia sql, esto evita inyecciones sql
            $prep->bindParam(':nombre', $nombre);
            $prep->bindParam(':correo', $correo);
            $prep->bindParam(':contrasena', $hashed_password); // Bind hashed contrasena
            $prep->bindParam(':cedula', $cedula);
            $prep->bindParam(':telefono', $telefono);
            $prep->bindParam(':id_rol', $id_rol, PDO::PARAM_INT); // Bind id_rol

            // ejecutamos la sentencia
            return $prep->execute(); // execute returns true on success

        }catch(PDOException $e){
            //se usa la funcion die en este proyecto para detener la ejecucion y mostrar el error, en un entorno real se registraria el error en lugar de mostrarlo
            die('Error al crear usuario: '. $e->getMessage());
        }
    }

    //obtiene todos los usuarios
    public function get_user(){
        try {
            $sql = "SELECT u.id, u.nombre, u.correo, u.cedula, u.telefono, r.nombre_rol as rol, u.estado 
                    FROM $this->nombreTabla u
                    JOIN roles r ON u.id_rol = r.id"; // Joining with roles table
            $result = $this->conexion->query($sql);
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al obtener usuarios: " . $e->getMessage());
        }
    }

    //obtiene un solo usuario por su id
    public function get_user_by_id($id){
        try{
            $sql = "SELECT u.id, u.nombre, u.correo, u.cedula, u.telefono, r.nombre_rol as rol, u.estado 
                    FROM $this->nombreTabla u
                    JOIN roles r ON u.id_rol = r.id
                    WHERE u.id = :id";
            $prepare = $this->conexion->prepare($sql);
            $prepare->bindParam(':id', $id, PDO::PARAM_INT);
            $prepare->execute();
            return $prepare->fetch(PDO::FETCH_ASSOC);
        }catch (PDOException $e) {
            die("Error al obtener usuario: " . $e->getMessage());
        }
    }

    //actualiza un usuario existente
    public function update_user($id, $nombre, $correo, $cedula, $telefono, $id_rol, $contrasena = null){
        try {
            $sql = "UPDATE $this->nombreTabla 
                    SET nombre = :nombre, 
                        correo = :correo, 
                        cedula = :cedula, 
                        telefono = :telefono, 
                        id_rol = :id_rol";
            
            if ($contrasena) {
                $sql .= ", contrasena = :contrasena";
            }
            $sql .= " WHERE id = :id";

            $prepare = $this->conexion->prepare($sql);

            $prepare->bindParam(':id', $id, PDO::PARAM_INT);
            $prepare->bindParam(':nombre', $nombre);
            $prepare->bindParam(':correo', $correo);
            $prepare->bindParam(':cedula', $cedula);
            $prepare->bindParam(':telefono', $telefono);
            $prepare->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
            if ($contrasena) {
                $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);
                $prepare->bindParam(':contrasena', $hashed_password);
            }

            return $prepare->execute();

        } catch (PDOException $e) {
            die("Error al actualizar usuario: " . $e->getMessage());
        }
    }

    // Método para iniciar sesión de un usuario
    public function login_user($correo, $contrasena){
        try {
            $sql = "SELECT id, nombre, correo, contrasena, cedula, telefono, id_rol FROM $this->nombreTabla WHERE correo = :correo";
            $prepare = $this->conexion->prepare($sql);
            $prepare->bindParam(':correo', $correo);
            $prepare->execute();
            $usuario = $prepare->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
                unset($usuario['contrasena']); // Do not return contrasena hash
                return $usuario;
            }
            return false;
        } catch (PDOException $e) {
            die("Error al iniciar sesión: " . $e->getMessage());
        }
    }

    //elimmina un usuario por id
    public function delete_user($id){
        try {
            $sql = "DELETE FROM $this->nombreTabla WHERE id = :id";

            $prepare = $this->conexion->prepare($sql);
            $prepare->bindParam(':id', $id);

            return $prepare->execute();
            
        } catch (PDOException $e) {
            die("Error al eliminar usuario: " . $e->getMessage());
        }
    }

    // Obtiene el número total de usuarios
    public function get_total_users(){
        try {
            $sql = "SELECT COUNT(id) FROM $this->nombreTabla";
            $result = $this->conexion->query($sql);
            return $result->fetchColumn();
        } catch (PDOException $e) {
            die("Error al obtener el total de usuarios: " . $e->getMessage());
        }
    }

    // Obtiene el número de usuarios por rol
    public function get_total_users_by_role($id_rol){
        try {
            $sql = "SELECT COUNT(id) FROM $this->nombreTabla WHERE id_rol = :id_rol";
            $prepare = $this->conexion->prepare($sql);
            $prepare->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
            $prepare->execute();
            return $prepare->fetchColumn();
        } catch (PDOException $e) {
            die("Error al obtener el total de usuarios por rol: " . $e->getMessage());
        }
    }
}