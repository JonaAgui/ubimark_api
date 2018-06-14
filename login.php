<?php
    header("Content-Type:application/json");
    include_once("db/conectar.php");
    include("funciones.php");
    $correo = $_POST['email'];
    $pass = $_POST['password'];
    if(notNull($correo) && notNull($pass)){
        login($correo,$pass,$enlace);
    }else{
        echo json_encode(response(302));
    }
    $enlace->close();

    /**
     * Funcion que genera el directorio correspondiente para guardar las imagenes
     * de los productos publicados en la cuenta personal
     *
     * @param integer $id
     * @return void
     */
    function generate_directory($id){
        $filename = "../intranet/usuarios/" . $id . "/uploads/";
        if (!file_exists($filename)) {
            mkdir($filename, 0777, true);
        }
    }

    /**
     * Función para iniciar sesión
     *
     * @param string $correo
     * @param string $pass
     * @param object $enlace
     * @return void
     */
    function login($correo,$pass,$enlace){
        $sql = "SELECT count(*) as found, Id_usuario, contrasena, trabaja_en FROM usuario WHERE correo = ?";
        if ($query = $enlace -> prepare($sql)) {
            $query -> bind_param("s", $correo);
            $query -> execute();
            $query -> bind_result($count, $id, $hash, $empresa);
            $query -> fetch();
            $query -> close();
        }else{
            echo json_encode(response(300, sqlError($sql, "s", $correo)));
        }
		
        if ($count==1 && password_verify($pass, $hash)){
            $token = bin2hex(random_bytes(128));
            $res = reg_active_session($id, $token);
            if($res['status_code'] != 200){
                echo json_encode($res);
                return;
            } 
            set_login_cookies($id, $token, $empresa);
            generate_directory($id);
            echo json_encode(response(200));	
        }else{
            echo json_encode(response(103));
        }  
    }

    /**
     * Funcion que registra en BD la sesion activa
     *
     * @return void
     */
    function reg_active_session($id,$token){
        $vigencia = time() + 3600;
        $date = new \DateTime('now', new \DateTimeZone('America/Mexico_City'));
        $date -> setTimestamp($vigencia);
        $expira = $date->format("Y-m-d H:i:s");
        $params = array (
            "token" => $token,
            "Id_usuario" => $id,
            "expira" => $expira
        );
        return dbInsert("sesiones_activas","sis",$params);
        
    }
    

    /**
     * Funcion que establece las cookies para mantener la sesion activa
     *
     * @param integer $id
     * @param string $token
     * @param integer $empresa
     * @return void
     */
    function set_login_cookies($id,$token,$empresa){
        setcookie('Activo',"True",time()+3600,'','','',TRUE);
        setcookie('Id',$id,time()+3600,'','','',TRUE);
        setcookie('token',$token,time()+3600,'','','',TRUE);
        setcookie('empresa',isset($empresa)?$empresa:0,time()+3600,'','','',TRUE);
    }
?>
