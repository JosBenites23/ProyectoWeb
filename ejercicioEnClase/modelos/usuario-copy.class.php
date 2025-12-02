<?php

    class Usuario{
        private $id ="";
        private $cedula ="0999999999";
        public $nombre ="jose";
        private $email ="";
        private $imagen ="";

        /*
        public function __construct($id, $cedula, $nombre, $email, $imagen){
            $this->id = $id;
            $this->cedula = $cedula;
            $this->nombre = $nombre;
            $this->email = $email;
            $this->imagen = $imagen;
        }*/

        public function __construct(){
            
        }

        public function saludo(){
            $this->formato("Hola, bienvenido $this->nombre con cedula $this->cedula");
        }

        private function formato($texto){
            echo "<div style='color:blue;'>$texto</div>";
        }

        /*
        public function getId(){
            return $this->id;
        }

        public function getNombre(){
            return $this->nombre;
        }

        public function getEmail(){
            return $this->email;
        }

        public function getPassword(){
            return $this->password;
        }*/
    }

    /*FUNDAMENTO*/
        $ObjUsuario = new Usuario();
        echo $ObjUsuario->nombre;
        $ObjUsuario->saludo();
?>