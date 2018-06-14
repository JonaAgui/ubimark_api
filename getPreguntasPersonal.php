<?PHP
    header("Content-type:application/json");
    include_once("conectar.php");
    include("funciones.php");
    $Id = $_COOKIE['Id'];


    $sql2 = "SELECT q.*,CONCAT(SUBSTRING_INDEX(u.nombre,' ',1),' ',SUBSTRING(u.apellidos,1,1),'.') as cliente 
            FROM preguntas q 
            JOIN usuario u ON u.Id_usuario = q.Id_cliente
            JOIN preguntas_personal  pp ON pp.Id_pregunta = q.Id_pregunta
            WHERE pp.Id_usuario = ?";
    if($query = $enlace -> prepare($sql2)){
        $query -> bind_param("i",$Id);
        $query -> execute();
        $res = $query -> get_result();
        $query -> close();
    }else{
        echo json_encode(response(300,sqlError($sql2,"i",$Id)));
        return;
    }
    $preguntas_prod = array();
    while($row = $res -> fetch_Assoc()){
        $sql3 = "SELECT * FROM respuestas WHERE Id_pregunta = ?";
        if($query = $enlace -> prepare($sql3)){
            $query -> bind_param("i",$row['Id_pregunta']);
            $query -> execute();
            $res2 = $query -> get_result();
            $query -> close();
        }else{
            echo json_encode(response(300,sqlError($sql3,"i",$row['Id_pregunta'])));
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
            if($query = $enlace -> prepare($sql4)){
                $query -> bind_param("i",$row['Id_producto']);
                $query -> execute();
                $res3 = $query -> get_result();
                $query -> close();
            }else{
                echo json_encode(response(300,sqlError($sql4,"i",$row['Id_producto'])));
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
    echo json_encode(response(200,$preguntas_prod));
?>