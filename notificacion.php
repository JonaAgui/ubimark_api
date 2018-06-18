<?PHP
    header("Content-Type:application/json");
    include("funciones.php");
    $acceptedMethods = array("GET","POST","PUT");
    $method = getMethod(); 
    $log  = getLogger("Notificacion");
    $db = getDBConnection();

    switch($method){
        case "GET":
            $log->trace("GET");
            $req = $_GET;
            $res = get($req);
            break;
        case "POST":  
            $req = getRequest($_POST,file_get_contents("php://input"));
            $log -> trace("POST");
            $res = post($req);
            break;
        case "PUT":
            $log -> trace("PUT");
            parse_str(file_get_contents("php://input"),$req);
            $req = getRequest($req,file_get_contents("php://input"));
            $res = put($req);
            break;
        default:
            $res = response(500,methodError($acceptedMethods,$method));
    }

    echo json_encode($res);

    function get($req){
        global $log;
        if(isset($req['notificacion'])){
            $log->trace ("Buscando notificacion");
            return getNoti($req);
        }else{
            return getAll($req);
        }
    }

    function getNoti($req){
        global $db;
        global $log;
        $id = $_COOKIE['Id'];
        $notificacion = $req['notificacion'];
        $sql = "SELECT * FROM notificaciones WHERE destino = ? AND Id_notificacion = ?";
        if($query = $db -> prepare($sql)){
            $query -> bind_param("ii",$id,$notificacion);
            $query -> execute();
            $res = $query -> get_result();
            $query -> close();
        }else{
            return response(300,sqlError($sql,"i",array("Destino"=>$id)));
        }
    
        $row = $res-> fetch_assoc();
        if(strcmp($row['tipo'],"PREGUNTA")==0){
            $sql2 = "SELECT p.pregunta,i.path,i.Id_usuario ,if(count(pe.Id_empresa)> 0, 'EMPRESA' , 'PERSONAL')as tipo_vendedor
                    FROM preguntas p 
                    JOIN imagen_prod i ON i.Id_producto = p.Id_producto
                    LEFT JOIN preguntas_empresa pe ON pe.Id_pregunta = p.Id_pregunta
                    LEFT JOIN preguntas_personal pp ON pp.Id_pregunta = p.Id_pregunta
                    WHERE p.Id_pregunta = ?";
            if($query = $db -> prepare($sql2)){
                $query -> bind_param("i",$row['origen']);
                $query -> execute();
                $query -> bind_result($mensaje,$ruta,$autor_img,$vendedor);
                $query -> fetch();
                $query -> close();
            }else{
                return response(300,sqlError($sql2,"i",array("Id_pregunta"=>$row['origen'])));
   
            }
            $row['mensaje'] = $mensaje;
            $row['ruta_img'] = $ruta;
            $row['autor_img'] = $autor_img;
            $notificacion = $row;
        }else if(strcmp($row['tipo'],"RESPUESTA")==0){
            $sql2 = "SELECT r.respuesta,i.path,i.Id_usuario ,if(count(pe.Id_empresa)> 0, 'EMPRESA' , 'PERSONAL')as tipo_vendedor
                    FROM respuestas r 
                    JOIN preguntas p ON r.Id_pregunta = p.Id_pregunta
                    JOIN imagen_prod i ON i.Id_producto = p.Id_producto
                    LEFT JOIN preguntas_empresa pe ON pe.Id_pregunta = p.Id_pregunta
                    LEFT JOIN preguntas_personal pp ON pp.Id_pregunta = p.Id_pregunta
                    WHERE r.Id_respuesta = ?";
            if($query = $db -> prepare($sql2)){
                $query -> bind_param("i",$row['origen']);
                $query -> execute();
                $query -> bind_result($mensaje,$ruta,$autor_img,$vendedor);
                $query -> fetch();
                $query -> close();
            }else{
                return response(300,sqlError($sql2,"i",array("Id_pregunta"=>$row['origen'])));
            }
            $row['mensaje'] = $mensaje;
            $row['ruta_img'] = $ruta;
            $row['autor_img'] = $autor_img;
            $notificacion = $row;
        }
        return response(200,$notificacion);
    }

    function getAll($req){
        global $db;
        global $log;
        $id = $_COOKIE['Id'];
        $sql = "SELECT * FROM notificaciones WHERE destino = ? AND estado != 'COMPLETADO' ORDER BY fecha DESC LIMIT 50";
        if($query = $db -> prepare($sql)){
            $query -> bind_param("i",$id);
            $query -> execute();
            $res = $query -> get_result();
            $query -> close();
        }else{
            return response(300,sqlError($sql,"i",array("Destino"=>$id)));
        }

        $notificaciones = array();
        while($row = $res-> fetch_assoc()){
            if(strcmp($row['tipo'],"PREGUNTA")==0){
                $sql2 = "SELECT p.pregunta,i.path,i.Id_usuario 
                        FROM preguntas p 
                        JOIN imagen_prod i ON i.Id_producto = p.Id_producto 
                        WHERE p.Id_pregunta = ?";
                if($query = $db -> prepare($sql2)){
                    $query -> bind_param("i",$row['origen']);
                    $query -> execute();
                    $query -> bind_result($mensaje,$ruta,$autor_img);
                    $query -> fetch();
                    $query -> close();
                }else{
                   return response(300,sqlError($sql2,"i",array("Id_pregunta"=>$row['origen'])));
                }
                $row['mensaje'] = $mensaje;
                $row['ruta_img'] = $ruta;
                $row['autor_img'] = $autor_img;
                array_push($notificaciones,$row);
            }else if(strcmp($row['tipo'],"RESPUESTA")==0){
                $sql2 = "SELECT r.respuesta,i.path,i.Id_usuario
                        FROM respuestas r 
                        JOIN preguntas p ON r.Id_pregunta = p.Id_pregunta
                        JOIN imagen_prod i ON i.Id_producto = p.Id_producto
                        WHERE r.Id_respuesta = ?";
                if($query = $db -> prepare($sql2)){
                    $query -> bind_param("i",$row['origen']);
                    $query -> execute();
                    $query -> bind_result($mensaje,$ruta,$autor_img);
                    $query -> fetch();
                    $query -> close();
                }else{
                    return response(300,sqlError($sql2,"i",array("Id_pregunta"=>$row['origen'])));
                }
                $row['mensaje'] = $mensaje;
                $row['ruta_img'] = $ruta;
                $row['autor_img'] = $autor_img;
                array_push($notificaciones,$row);
            }
        }
        return response(200,$notificaciones);

    }

    function post($req){
        $required = array("NotNull" => array("tipo", "remitente","origen","destino","fecha","tipo_destino"));
        $res = paramsRequired($req,$required);
        if($res['status_code']!=200){
            return $res;
        }
        $params = $req;
        $params['estado'] = "NO_LEIDO";
    
        $types = "siiisss";
    
        $result = dbInsert("notificaciones",$types,$params);
        if($result['status_code']!=200){
            return $result;
        }else{
            $params['Id_notificacion'] = $result['data']['ID'];
        }
    
        
        return response(200,$params);
    }
    function put($req){
        $required = array("NotNull" => array("notificaciones", "estado"));
        $res = paramsRequired($req,$required);
        if($res['status_code']!=200){
            return $res;
        }
        $notificaciones = $req['notificaciones'];
        $estado = array("estado" => $req['estado']);
        foreach ($notificaciones as $notificacion) {
            $res = dbUpdate("notificaciones","s",$estado,"i",array("Id_notificacion"=>$notificacion));
            if($res['status_code']!=200){
                return $res;
            }
        }
        return response(200);
    }
    function delete($req){
        
    }