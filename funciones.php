<?php
    include_once("db/conectar.php");
    include('lib/log4php/Logger.php');
   
    $dblogger;
    $log = getLogger("class");
    function acceptedsParamError($acceptedParams,$param){
        return array("Parametros aceptados" => $acceptedParams, "Parametro" => $param);
    }

    function acceptedValueError($acceptedValues,$value){
        return array("Valores Aceptados" => $acceptedValues, "Valor" => $value);
    }

    function arr2str($arr){
        $str = "";
        foreach($arr AS $key => $val){
            $str .= $key."=>".$val.",";
        }
        return $str;
    }


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
        global $log;
        $link = getDBConnection();
        $sql = "DELETE FROM ".$table." WHERE ";
        $values = array();
        foreach($params as $key => $val){
            array_push($values,$val);
            $sql .= $key . " = ? AND ";
        }
        $sql = substr($sql,0,(strlen($sql)-4));
        if(strlen($types) != count($values)){
            $log->warn("Los cantidad de datos esperados y recibidos no concuerdan");
            return response(301);
        }
        $a_params = array();
        $a_params[] = & $types;
        for($i = 0; $i < count($values); $i++) {
            $a_params[] = & $values[$i];
        }
        $log->info($sql." ". arr2str($a_params));
        if($query = $link->prepare($sql)){
            call_user_func_array(array($query,'bind_param'),$a_params);
            $query->execute();
            $query->close();
        }else{
            $log->error("Ocurrio un error en la consulta" . sqlError($sql,$types,$values));
            return response(300);
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
        global $log;
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
            $log->warn("Los cantidad de datos esperados y recibidos no concuerdan");
            return response(301);
        }
        
        $a_params = array();
        $a_params[] = & $types;
        for($i = 0; $i < count($values); $i++) {
            $a_params[] = & $values[$i];
        }
        $log->info($sql." ". arr2str($a_params));
        if($query = $link->prepare($sql)){
            call_user_func_array(array($query,'bind_param'),$a_params);
            $query->execute();
            $new_id = $query->insert_id;
            $query->close();
        }else{
            $log->error("Ocurrio un error en la consulta" . arr2str(sqlError($sql,$types,$values)));
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
            $sql .= $key . " = ? AND ";
        }
        $sql = substr($sql,0,(strlen($sql)-4));
        $types .= $target_types;
        //Comprueba que los datos tengan su tipo correspondiente
        if(strlen($types) != count($values)){
            $log->warn("Los cantidad de datos esperados y recibidos no concuerdan");
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
            $log->error("Ocurrio un error en la consulta" . sqlError($sql,$types,$values));
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
        $dblogger =  Logger::getLogger("db.".$class);
        return Logger::getLogger($class);
    }

    function getDBLogger(){
        global $dblogger;
        return $dblogger;
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

    function getTypes($params,$types_ref){
        $types="";
        foreach($params as $key => $val){
            $types .= $types_ref[$key];
        }
        return $types;
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



    function tag_exist($tag,$tags,$db){
        $c = 0;
        $tag="%".$tag."%";
        $params = "s";
        $sql="SELECT Id_tag FROM tags WHERE tag LIKE ?";
        if(count($tags)>0){
            $sql.=" AND Id_tag NOT IN(?,";
            $params=$params."i";
            for($i=0;$i<(count($tags)-1);$i++){
                $sql = $sql."?,";
                $params=$params."i";
            }
            $sql=substr($sql,0,strlen($sql)-1).")";
        }
        $a_params=array();
        $a_params[] = & $params;
        $a_params[] = & $tag;
        for($i = 0; $i < count($tags); $i++) {
            $a_params[] = & $tags[$i];
        }
        if($query=$db->prepare($sql)){
            call_user_func_array(array($query,'bind_param'),$a_params);
            $query->execute();
            $query->bind_result($c);
            $query->fetch();
            $query->close();
        } 
        //echo $tag." ".$c."<br>";
        return $c;
    }

    //Funcion que obtiene los productos pertenecientes a $a\$e 
    function multiTag2Product($a,$e,$db){
        //Define la cantidad de elementos del conjunto $a y genera la instruccion sql
        $n=count($a);
        
        $sql="SELECT Id_producto FROM map_tag where Id_tag IN (?,";
        $params="s";
        for($i=0;$i<($n-1);$i++){
            $sql = $sql."?,";
            $params=$params."s";
        }
        $sql=substr($sql,0,strlen($sql)-1).")";
        //Define la cantidad de elementos del conjunto $e y genera la instruccion sql
        if(count($e)>0){
            $sql.=" AND Id_producto NOT IN(?,";
            $params=$params."s";
            for($i=0;$i<(count($e)-1);$i++){
                $sql = $sql."?,";
                $params=$params."s";
            }
        }
        $sql=substr($sql,0,strlen($sql)-1).") GROUP BY Id_producto HAVING COUNT(Id_producto)=".$n;
        //crea el arreglo con $aU$b y el string correspondiente a la cantidad de parametros esperados
        $a_params=array();
        $a_params[] = & $params;
        if($n>1){
            for($i = 0; $i < $n; $i++) {
                $a_params[] = & $a[$i];
            }
        }else{
            $a_params[] = & $a[0];
        }
        
        for($i = 0; $i < count($e); $i++) {
          $a_params[] = & $e[$i];
        }
        //ejecuta la consulta sql preparada y devuelve $a\$b
        if($query=$db->prepare($sql)){
            call_user_func_array(array($query,'bind_param'),$a_params);
            $query->execute();
            $res=$query->get_result();
            $query->close();
        }
        return $res;
        
    }


    function buscar($token,$results = array()){
        $db = getDBConnection();
        $arr = preg_split("/[\s,]+/",$token);

        //Se realiza la primera busqueda en el nombre del producto y se almacenan los resultados
        $prod="%".$token."%";
        if($query=$db->prepare("SELECT Id_producto FROM productos WHERE nombre_producto LIKE ?")){
            $query->bind_param("s",$prod);
            $query->execute();
            $res=$query->get_result();
            $query->close();
        }
        while($row=$res->fetch_Assoc()){
            array_push($results,$row['Id_producto']);
        }

        $tags= array();
        //Valida si una de las etiquetas encontradas en la busqueda existe de no ser el caso realiza transformaciones y compara nuevamente
        foreach($arr as &$val){
            $val=strtoupper($val);
            if (($id_tag=tag_exist($val,$tags,$db))> 0){
                array_push($tags,$id_tag);
            }else{
                if(strrpos($val,'ES')==strlen($val)-2&&strrpos($val,'ES')>0){
                    $temp=substr($val,0,strlen($val)-2);
                    if(strrpos($temp,'C')==strlen($temp)-1){
                        $temp=substr($temp,0,strlen($temp)-1)."Z";
                    }
                    if(($id_tag=tag_exist($temp,$tags,$db))>0){
                        array_push($tags,$id_tag);
                    }
                }else if(strrpos($val,'IS')==strlen($val)-2&&strrpos($val,'IS')>0){
                    $temp=substr($val,0,strlen($val)-2)."Y";
                    if(($id_tag=tag_exist($temp,$tags,$db))>0){
                        array_push($tags,$id_tag);
                    }
                }else if(strrpos($val,'S')==strlen($val)-1){
                    $temp=substr($val,0,strlen($val)-1);
                    if(($id_tag=tag_exist($temp,$tags,$db))>0){
                        array_push($tags,$id_tag);
                    }else{
                        $val=$temp;
                    }
                }
                if(strrpos($val,"A")==strlen($val)-1||strrpos($val,"O")==strlen($val)-1){
                    $temp=substr($val,0,strlen($val)-1);
                    if(($id_tag=tag_exist($temp,$tags,$db))>0){
                        array_push($tags,$id_tag);
                    }
                }
            }
        }
        // se establece los datos para realizar la combinatoria sin repeticion del conjunto de etiquetas y se genera el arreglo de resultados que contendra al conjunto de elementos a excluir
        $n=count($tags)-1;
        $m=count($tags);
        
        //Se comprueba que la busqueda contenga al menos una etiqueta valida
        if(count($tags)>0){
            //Se realiza la primera busqueda por etiquetas donde el producto coincida con cada etiqueta en $tags y se almacenan los resultados
            $temp=multiTag2Product($tags,$results,$db);
            while($row=$temp->fetch_Assoc()){
                array_push($results,$row['Id_producto']);
            }
            //Comienza la combinatoria de ser necesario solo si hay por lo menos 3 etiquetas validas
            while($n>1){
                $tags2use=array(); //etiquetas por combinacion
                $count=array(); //contador
                $is=range(0,$n-1); //indices de 0 a $n-1
                for($i=0;$i<$n;$i++){
                    array_push($count,0); //se establece el contador en 0 para cada indice
                }
                $i=count($is)-1; //apuntador al ultimo indice
                while($is[0]<$m-$n){ //se comprueba que la posicion del primer indice
                    if($is[$i]<=$m-($m-($i+($m-$n)))){ //se comprueba que el apuntador actual no haya alcanzado el limite
                        foreach($is as &$temp){ 
                            array_push($tags2use, $tags[$temp]);//se toman los indices para obtener una combinacion de $n etiquetas
                        } 
                        //Se realiza la busqueda de productos que coinciden con n etiquetas
                        $r=multiTag2Product($tags2use,$results,$db);
                        while($row=$r->fetch_Assoc()){
                            array_push($results,$row['Id_producto']);
                        }
                        $tags2use=array(); //Se espera la siguiente combinacion
                        $is[$i]++; 
                        $count[$i]++;                
                    }else{ //En caso de que el apuntador actual alcanzara el limite
                        $is[$i]--;
                        $count[$i]--;
                        if($count[$i]>1){ 
                            $is[$i]-=($count[$i]-1);
                            $count[$i]=0;
                            for($j=$i;$j<count($count)-1;$j++){
                                $is[$j]-=($count[$i]-1);
                                $count[$j]=0;
                            }
                            $i--;
                            $is[$i]++;
                            $count[$i]++;
                            $i=count($is)-1;
                        }else{
                            $i--;
                            $is[$i]++;
                            $count[$i]++;
                            if(!$is[0]<$m-$n){
                                foreach($is as &$temp){
                                    if(isset($tags[$temp])){
                                        array_push($tags2use, $tags[$temp]);
                                    }
                                }               
                                $r=multiTag2Product($tags2use,$results,$db);
                                while($row=$r->fetch_Assoc()){
                                    array_push($results,$row['Id_producto']);
                                }
                                $tags2use=array();
                            }
                        }
                    }
                }
                $n--;
                
            }
            //Ultima busqueda por etiquetas en caso de que se encontrara por lo menos dos etiquetas validas busca coincidencia con al menos 1 etiqueta
            if($n===1){
                foreach($tags as &$temp){
                    $t=array();
                    array_push($t,$temp);
                    $r=multiTag2Product($t,$results,$db);
                    while($row=$r->fetch_Assoc()){
                        array_push($results,$row['Id_producto']);
                    }
                }
            }
        }
        return $results;
    }

?>