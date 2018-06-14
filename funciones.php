<?php
    include_once("db/conectar.php");
    include('lib/log4php/Logger.php');

    function check_session(){
        $link = getDBConnection();
        if(!isset($_COOKIE['Id']) || !isset($_COOKIE['token'])){
            return response(100,"COOKIES FALTANTES");
        }
        $date = new \DateTime('now', new \DateTimeZone('America/Mexico_City'));
        $id = $_COOKIE['Id'];
        $token = $_COOKIE['token'];
        $sql = "SELECT expira 
                FROM sesiones_activas 
                WHERE Id_usuario = ? AND token = ?";

        if($query = $link->prepare($sql)){
            $query->bind_param("is",$id,$token);
            $query->execute();
            $res = $query->get_result();
            $query->close();
            while($row = $res -> fetch_Assoc()){
                $actual = time();
                $actual = $date->format("Y-m-d H:i:s");
                if($actual<$row['expira']){
                    $row['user']=$id;
                    $row['empresa']=isset($_COOKIE['empresa'])?$_COOKIE['empresa']:0;
                    return response(101, $row);
                }
            } 
            return response(100);
            
        }else{
            return response(300,sqlError($sql,"is",array("Id_usuario"=>$id,"token"=>$token)));
        }
    }
    
    /**
     * Funcion para eliminar de cualquier tabla de la bs
     *
     * @param string $table Nombre de la tabla en BD
     * @param string $types Tipos de los datos
     * @param array $params Diccionario de datos para eliminar
     * @return array Respuesta en formato json 
     */
    function dbDelete($table,$types,$params){
        $link = getDBConnection();
        $sql = "DELETE FROM ".$table." WHERE ";
        $values = array();
        foreach($params as $key => $val){
            $sql .= $key." = ? ";
            array_push($values,$val);
        }
        if(strlen($types) != count($values)){
            return response(301);
        }
        $a_params = array();
        $a_params[] = & $types;
        for($i = 0; $i < count($values); $i++) {
            $a_params[] = & $values[$i];
        }
        if($query = $link->prepare($sql)){
            call_user_func_array(array($query,'bind_param'),$a_params);
            $query->execute();
            $query->close();
        }else{
            return response(300,sqlError($sql,$types,$values));
        }
        return response(200);
    }

    /**
     * Función para insertar datos en cualquier tabla de la bd
     *
     * @param string $table Nombre de la tabla en BD
     * @param string $types Tipos de los datos a insertar
     * @param array $params Diccionario de datos a insertar
     * @return array Respuesta en formato json 
     */
    function dbInsert($table, $types, $params){
        $link = getDBConnection();
        $sql = "INSERT INTO " . $table . " ";
        $columnas = "";
        $valores = "";
        $values = array();
        foreach($params as $key => $val){
            $columnas .= $key.", ";
            $valores .= "?, ";
            array_push($values,$val);
        }
        $columnas = substr($columnas, 0, (strlen($columnas) - 2));
        $valores = substr($valores, 0, (strlen($valores) - 2));
        $sql .= "(" . $columnas . ") VALUES (" . $valores .  ")";

        //Comprueba que los datos tengan su tipo correspondiente
        if(strlen($types) != count($values)){
            return response(301);
        }
        
        $a_params = array();
        $a_params[] = & $types;
        for($i = 0; $i < count($values); $i++) {
            $a_params[] = & $values[$i];
        }
        if($query = $link->prepare($sql)){
            call_user_func_array(array($query,'bind_param'),$a_params);
            $query->execute();
            $new_id = $query->insert_id;
            $query->close();
        }else{
            return response(300,sqlError($sql,$types,$values));
        }
        return response(200,array("ID" => $new_id));
    }

    /**
     * Función para actualizar cualquier tabla de la bd
     *
     * @param string $table Tabla de la bd
     * @param string $types Tipos de los datos a actualizar
     * @param array $params Diccionario de datos a actualizar 
     * @param string $target_types Tipos de los datos con los que se buscará el registro
     * @param array $target Diccionario de los datos con los que se buscará el registro
     * @return array Respuesta en formato json 
     */
    function dbUpdate($table,$types,$params,$target_types,$target){
        $link = getDBConnection();
        //Se genera la consulta sql y se da formato a los datos a actualizar
        $sql = "UPDATE " . $table . " SET ";
        $values = array();
        foreach($params as $key => $val){
            array_push($values,$val);
            $sql .= $key . " = ?, ";
        }
        $sql = substr($sql,0,(strlen($sql)-2));
        $sql .= " WHERE ";
        foreach($target as $key => $val){
            array_push($values,$val);
            $sql .= $key . " = ?, ";
        }
        $sql = substr($sql,0,(strlen($sql)-2));
        $types .= $target_types;
        //Comprueba que los datos tengan su tipo correspondiente
        if(strlen($types) != count($values)){
            return response(301);
        }
        
        $a_params = array();
        $a_params[] = & $types;
        for($i = 0; $i < count($values); $i++) {
            $a_params[] = & $values[$i];
        }
        if($query = $link->prepare($sql)){
            call_user_func_array(array($query,'bind_param'),$a_params);
            $query->execute();
            $query->close();
        }else{
            return response(300,sqlError($sql,$types,$values));
        }
        return response(200);
    }
    
    /**
     * Función que genera la instancia del Logger
     * 
     * @param string LoggerName
     * @return object
     */
    function getLogger($class){
        Logger::configure('config.xml');
        return Logger::getLogger($class);;
    }

    function getMethod(){
        return $_SERVER['REQUEST_METHOD'];
    }

    function getRequest($req,$fileContents){
        $JSON = json_decode($fileContents, true);
        if(isset($JSON) && $JSON != null){
            $req = $JSON;
        }
        return $req;
    }

    function methodError($acceptedMethods, $method){
        return array("Accepted" => $acceptedMethods, "Request_method" => $method);
    }

    /**
     * Función para comprobar si un valor no es nulo.
     * 
     * @param mixed $value Valor a comprobar
     * @return boolean 
     */
    function notNull($value){
        if(is_string($value)){
            return $value != null && trim($value) != '';
        }
        return $value != null;
    }

    function paramsRequired($params,$required){
        global $log;
        foreach($required as $null => $arr){
            foreach ($arr as $key ) {
                if(!isset($params[$key])){
                    $log -> error("Missing param in request ". $key);
                    return response(306,$key);
                }
                $temp = $params[$key];
                if(!notNull($temp) && strcmp($null,"NotNull") == 0){
                    $log -> error("Null data in param ".$key);
                    return response(302,$key);
                }
            }
        }
        $log->info("Well done");
        return response(200);

    }

    /**
     * Función para dar respuestas en json.
     * 
     * Función que genera la estructura de json para dar respuesta a las peticiones
     * recibidas siguiendo un estandar establecido
     * 
     * @param integer $code
     * @param mixed $data
     * @return array respuesta_json
     */
    function response($code, $data=null){
        $def_messages=array(
            "100" => "Sesion cerrada",
            "101" => "Sesion activa",
            "102" => "El correo ya se encuentra registrado",
            "103" => "Usuario no encontrado",
            "104" => "Empresa no registrada",
            "105" => "Empresa registrada",
            "106" => "Acceso denegado",
            "107" => "Acceso permitido",
            "108" => "Pagina sin acceso definido",
            "200" => "Hecho.",
            "201" => "Hecho. Faltan datos en la bd.",
            "300" => "Error al mandar la consulta a la bd.",
            "301" => "Los tipos y la cantidad de datos en la peticion no concuerdan.",
            "302" => "Campo nulo en la peticion.",
            "303" => "El producto ya se encuentra en el carrito",
            "304" => "No hay existencias suficientes para procesar la compra",
            "305" => "No se encontraron datos",
            "306" => "Faltan campos en la peticion",
            "400" => "Petición al socket no valida",
            "500" => "Metodo de petición invalido",
        );
        $arr = array(
            "status_code" => $code,
            "message" => $def_messages[$code],
            "data" => $data,
            "version" => "2.1.0"
        );
        return $arr;
    }
    
    function sqlError($sql,$types,$params){
        $error = array(
            "sql" => $sql,
            "types" => $types,
            "params" => $params,
        );
        return $error;
    }

?>