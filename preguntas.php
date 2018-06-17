<?PHP
    header("Content-Type:application/json");
    include("funciones.php");
    $acceptedMethods = array("GET","POST");
    $method = getMethod(); 
    $log  = getLogger("Preguntas");
    $db = getDBConnection();

    switch($method){
        case "GET":
            $log->trace("GET");
            $req = $_GET;
            $res = get($req);
            break;
        case "POST":  
            $log -> trace("POST");
            $req = getRequest($_POST,file_get_contents("php://input"));
            $res = post($req);
            break;
        default:
            $res = response(500,methodError($acceptedMethods,$method));
    }

    echo json_encode($res);

    function get($req){
        global $log;
        
        $acceptedParams=array("Id_producto","tipo");
        if(isset($req['Id_producto'])){
            $log->info("Preguntas por producto");
            return getXProducto($req);
        }else if(isset($req['tipo'])){
            $accepted_types = array("EMPRESA","PERSONAL");
            switch ($req['tipo']){ 
                case "EMPRESA":
                    $log->info("Preguntas por empresa");
                    return getEmpresa($req);
                case "PERSONAL":
                    $log->info("Preguntas por personal");
                    return getPersonal($req);
                    break;
                default:
                    $log->warn("Tipo incorrecto");
                    return response(501, acceptedValueError($accepted_types,$req['tipo']));
            }
        }else{
            $log->warn("Parametros incorrectos");
            return response(306, acceptedParamsError($acceptedParams,$req[0]));
        }
        
    }

    function getXProducto($req){
        global $db;
        global $log;
        $Id_producto = $req['Id_producto'];
        $sql = "SELECT p.Id_pregunta,p.pregunta,p.fecha, CONCAT(SUBSTRING_INDEX(u.nombre,' ',1),' ',SUBSTRING(u.apellidos,1,1),'.') AS cliente 
                FROM preguntas AS p 
                JOIN usuario AS u ON u.Id_usuario = p.Id_cliente 
                WHERE p.Id_producto = ? 
                ORDER BY p.Id_pregunta DESC" ;
        if($query = $db -> prepare($sql)){
            $query -> bind_param("i",$Id_producto);
            $query -> execute();
            $res = $query -> get_result();
            $query -> close();
        }else{
            return response(300,sqlError($sql,"i",$Id_producto));
        }
        $preguntas = array();
        while($row = $res -> fetch_Assoc()){
            $row['fecha'] = get_diferencia($row['fecha']);
            $sql2 = "SELECT * 
                    FROM respuestas 
                    WHERE Id_pregunta = ?";
            if($query = $db -> prepare($sql2)){
                $query -> bind_param("i",$row['Id_pregunta']);
                $query -> execute();
                $res2 = $query -> get_result();
                $query -> close();
            }else{
                return response(300,sqlError($sql,"i",$row['Id_pregunta']));
            }
            $row['respuestas'] = array();
            while($row2 = $res2->fetch_Assoc()){
                array_push($row['respuestas'],$row2);
            }
            array_push($preguntas,$row);
        }
        if($preguntas != []){
            return response(200,$preguntas);
        }else{
            return response(305);
        }
        
        return;
        
    }

    function get_diferencia($fecha){
        $date = new \DateTime('now', new \DateTimeZone('America/Mexico_City'));
        $fecha = new DateTime($fecha);
        $actual = time();
        $actual = $date->format("Y-m-d H:i:s");
        $actual = new DateTime($actual);
        $diff = $fecha -> diff($actual);
        if(strcmp($diff->format('%m'), "0")!=0){
            return $fecha -> format("d-m-Y");
        }else if(strcmp($diff->format('%d'), "1")==0){
            return $diff->format('hace %d dia');
        }else if(strcmp($diff->format('%d'), "0")!=0){
            return $diff->format('hace %d días');
        }else if(strcmp($diff->format('%h'), "1")==0){
            return $diff->format('hace %h hora');
        }else if(strcmp($diff->format('%h'), "0")!=0){
            return $diff->format('hace %h horas');
        }else if(strcmp($diff->format('%i'), "1")==0){
            return $diff->format('hace %i minuto');
        }else if(strcmp($diff->format('%i'), "0")!=0){
            return $diff->format('hace %i minutos');
        }else if(strcmp($diff->format('%s'), "1")==0){
            return $diff->format('hace %s segundo');
        }else if(strcmp($diff->format('%s'), "0")!=0){
            return $diff->format('hace %s segundos');
        }else if(strcmp($diff->format('%s'), "0")==0){
            return $diff->format('justo ahora');
        }
    }

    function getEmpresa($req){
        $link = getDBConnection();
        $Id = $_COOKIE['Id'];

        $sql = "SELECT trabaja_en 
                FROM usuario 
                WHERE Id_usuario = ?";
        if($query = $link -> prepare($sql)){
            $query -> bind_param("i",$Id);
            $query -> execute();
            $query -> bind_result($Id_vendedor);
            $query -> fetch();
            $query -> close();
        }else{
            return response(300,sqlError($sql,"i",$Id));
            
        }
        $sql2 = "SELECT q.*, CONCAT(SUBSTRING_INDEX(u.nombre,' ',1),' ',SUBSTRING(u.apellidos,1,1),'.') as cliente 
                FROM preguntas q 
                JOIN usuario u ON u.Id_usuario = q.Id_cliente 
                JOIN preguntas_empresa pe ON  q.id_pregunta = pe.id_pregunta
                WHERE pe.Id_empresa = ?";
        if($query = $link -> prepare($sql2)){
            $query -> bind_param("i",$Id_vendedor);
            $query -> execute();
            $res = $query -> get_result();
            $query -> close();
        }else{
            return response(300,sqlError($sql2,"i",$Id_vendedor));
            
        }
    
        $preguntas_prod = getRespuestas($res);
        return response(200,$preguntas_prod);
    }

    function getPersonal($req){
        global $db;
        global $log;
        $Id = $_COOKIE['Id'];

        $sql2 = "SELECT q.*,CONCAT(SUBSTRING_INDEX(u.nombre,' ',1),' ',SUBSTRING(u.apellidos,1,1),'.') as cliente 
                FROM preguntas q 
                JOIN usuario u ON u.Id_usuario = q.Id_cliente
                JOIN preguntas_personal  pp ON pp.Id_pregunta = q.Id_pregunta
                WHERE pp.Id_usuario = ?";
        if($query = $db -> prepare($sql2)){
            $query -> bind_param("i",$Id);
            $query -> execute();
            $res = $query -> get_result();
            $query -> close();
        }else{
            return response(300,sqlError($sql2,"i",$Id));
            return;
        }
        $preguntas_prod = getRespuestas($res);
        
        return response(200,$preguntas_prod);
    }

    function getRespuestas($res){
        global $db;
        $preguntas_prod = array();
        while($row = $res -> fetch_Assoc()){
            $sql3 = "SELECT * 
                    FROM respuestas 
                    WHERE Id_pregunta = ?";
            if($query = $db -> prepare($sql3)){
                $query -> bind_param("i",$row['Id_pregunta']);
                $query -> execute();
                $res2 = $query -> get_result();
                $query -> close();
            }else{
                return response(300,sqlError($sql3,"i",$row['Id_pregunta']));
                return;
            }
            $cont = 0;
            $respuestas = array();
            while($row2 = $res2 -> fetch_Assoc()){
                $cont++;
                array_push($respuestas,$row2);
            }
            
    
            if(!isset($preguntas_prod[$row['Id_producto']])){
                $preguntas_prod[$row['Id_producto']] = array();
                
                $sql4 = "SELECT p.*,i.path,i.Id_usuario AS usr_path 
                        FROM productos p 
                        JOIN imagen_prod i ON i.Id_producto = p.Id_producto 
                        WHERE p.Id_producto = ?";
                if($query = $db -> prepare($sql4)){
                    $query -> bind_param("i",$row['Id_producto']);
                    $query -> execute();
                    $res3 = $query -> get_result();
                    $query -> close();
                }else{
                    return response(300,sqlError($sql4,"i",$row['Id_producto']));
                    return;
                }
    
                $row3 = $res3 -> fetch_Assoc();
                foreach($row3 as $key => $val){
                    $preguntas_prod[$row['Id_producto']][$key] = $val;
                }
                $preguntas_prod[$row['Id_producto']]['completadas'] = 0;
                $preguntas_prod[$row['Id_producto']]['pendientes'] = 0;
                $preguntas_prod[$row['Id_producto']]['preguntas'] = array();
            }
            if ($cont>0){
                $row['respuestas'] = $respuestas;
                $preguntas_prod[$row['Id_producto']]['completadas']++;
                $row['estado'] = "completado";
            }else{
                $preguntas_prod[$row['Id_producto']]['pendientes']++;
                $row['estado'] = "pendiente";
            }
            
            
            array_push($preguntas_prod[$row['Id_producto']]['preguntas'], $row);
            
        }
        return $preguntas_prod;
    }

    function post($req){
        global $db;
        global $log;

        $params = $req;
        $params['Id_cliente'] = $_COOKIE['Id'];
        $date = new \DateTime('now', new \DateTimeZone('America/Mexico_City'));
        $actual = time();
        $actual = $date->format("Y-m-d H:i:s");
        $params['fecha'] = $actual;
        $sql = "SELECT * 
                FROM productos 
                WHERE Id_producto = ?";
        if ($query = $db -> prepare($sql)){
            $query -> bind_param("i",$params['Id_producto']);
            $query -> execute();
            $res = $query -> get_result();
            $query -> close();
        }else{
            return response(300,sqlError($sql,"i",$params['Id_producto']));
        }
        $row = $res -> fetch_Assoc();

        if(strcmp($row['tipo_cuenta'], "PERSONAL") == 0){
            $tipo_vendedor = $row['tipo_cuenta'];
            $Id_vendedor = $row['Id_usuario'];
        }else if(strcmp($row['tipo_cuenta'], "EMPRESA") == 0){
            
            $tipo_vendedor = $row['tipo_cuenta'];
            $Id_vendedor = $row['Id_empresa'];

        }
        
        $types="";
        foreach($params as $key => $val){
            if(strcmp($key,"Id_producto")==0||strcmp($key,"Id_cliente")==0||strcmp($key,"Id_vendedor")==0){ 
                $types .= "i";
            }else{
                $types .= "s";
            }
        }
        $result = dbInsert("preguntas",$types,$params);
        if($result['status_code']!=200){
            return $result;
        }
        $types = "ii";
        if($tipo_vendedor == "EMPRESA"){
            $params2 = array("Id_pregunta" => $result['data']['ID'], "Id_empresa" => $Id_vendedor);
            $table = "preguntas_empresa";
        }else{
            $params2 = array("Id_pregunta" => $result['data']['ID'], "Id_usuario" => $Id_vendedor);
            $table = "preguntas_personal";
            
        }
        $result2 = dbInsert($table,$types,$params2);
        if($result2['status_code'] != 200){
            return $result2;
        }
    
        $respuesta = array("user"=> $params['Id_cliente'], "pregunta" => $params['pregunta'], "target" => $result['data']['ID'], 
                "fecha" =>$params['fecha'],"destino"=>$Id_vendedor,"tipo"=>$tipo_vendedor);
        return response(200,$respuesta);
    }