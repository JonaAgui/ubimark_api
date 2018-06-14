<?PHP
    header("Content-type:application/json");
    include("funciones.php");
    
    $acceptedMethods = array("GET");
    if($_SERVER['REQUEST_METHOD'] === 'GET'){
        $res = get($_GET);
    }else{
        $res = json_encode(response(500, methodError($acceptedMethods, $_SERVER['REQUEST_METHOD'])));
    }

    echo $res;

    function get($req){
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
            return json_encode(response(300,sqlError($sql,"i",$Id)));
            
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
            return json_encode(response(300,sqlError($sql2,"i",$Id_vendedor)));
            
        }
    
        $preguntas_prod = array();
        while($row = $res -> fetch_Assoc()){
            $sql3 = "SELECT * 
                    FROM respuestas 
                    WHERE Id_pregunta = ?";
            if($query = $link -> prepare($sql3)){
                $query -> bind_param("i",$row['Id_pregunta']);
                $query -> execute();
                $res2 = $query -> get_result();
                $query -> close();
            }else{
                return json_encode(response(300,sqlError($sql3,"i",$row['Id_pregunta'])));
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
                if($query = $link -> prepare($sql4)){
                    $query -> bind_param("i",$row['Id_producto']);
                    $query -> execute();
                    $res3 = $query -> get_result();
                    $query -> close();
                }else{
                    return json_encode(response(300,sqlError($sql4,"i",$row['Id_producto'])));
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
        return json_encode(response(200,$preguntas_prod));
    }
   
?>