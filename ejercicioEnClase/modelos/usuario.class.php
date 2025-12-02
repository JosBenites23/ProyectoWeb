<?php

    class Usuario{
        private $id;
        private $cedula;
        private $nombre;
        private $email;
        private $imagen;
        private $rutaArchivo="";

        public function SetRutaArchivo($rutaArchivo){
            $this->rutaArchivo = $rutaArchivo;
        }

        public function SetValores($cedula, $nombre, $email, $imagen){
            $this->cedula = str_replace(",", "", trim($cedula));
            $this->nombre = str_replace(",", "", trim($nombre));
            $this->email = str_replace(",", "", trim($email));
            $this->imagen = str_replace(",", "", trim($imagen));
        }

        public function SaveValores(){
            $archivo = fopen($this->rutaArchivo . "usuarios.csv", "a");
            fwrite($archivo, $this->cedula . "," . $this->nombre . "," . $this->email . "," . $this->imagen . PHP_EOL);
            fclose($archivo);
        }

        public function GetValores(){
            $archivo = fopen($this->rutaArchivo . "usuarios.csv", "r");
            $registros = array();
            while(!feof($archivo)){
                $linea = fgets($archivo);
                if($linea != ""){
                    $registros[] = explode(",", $linea);
                }
            }
            fclose($archivo);
            return $registros;
        }
        /*
         public function GetValores(){
            $archivo = fopen($this->rutaArchivo . "usuarios.csv", "r");
            $registros = array();
            while(!feof($archivo)){
                $linea = fgets($archivo);
                if(trim($linea) != ""){
                    $partes = explode(",", trim($linea));
                    if (count($partes) >= 4) {
                        $registro = array(
                            "cedula" => $partes[0],
                            "nombre" => $partes[1],
                            "email"  => $partes[2],
                            "imagen" => $partes[3]
                        );
                        $registros[] = $registro;
                    }
                }
            }
            fclose($archivo);
            return $registros;
        }
        */
    }

    /*FUNDAMENTO
    $ObjUsuario = new Usuario();
    $ObjUsuario->SetValores("12345678", "    J,uan", "juan@juan.co,m      ", "ju,an.jpg");
    $ObjUsuario->SetRutaArchivo("../public/");
    $ObjUsuario->SaveValores();
    $registros = $ObjUsuario->GetValores();    
    */
?>