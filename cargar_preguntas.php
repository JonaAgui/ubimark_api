<?PHP 
    header("Content-Type:application/json");
    include_once("db/conectar.php");
    include_once("funciones.php");
   
    $Id_producto = $_POST['Id_producto'];
    $sql = "SELECT p.Id_pregunta,p.pregunta,p.fecha, CONCAT(SUBSTRING_INDEX(u.nombre,' ',1),' ',SUBSTRING(u.apellidos,1,1),'.') as cliente 
            FROM preguntas as p 
            JOIN usuario as u ON u.Id_usuario = p.Id_cliente 
            WHERE p.Id_producto = ? 
            ORDER BY p.Id_pregunta DESC" ;
    if($query = $enlace -> prepare($sql)){
        $query -> bind_param("i",$Id_producto);
        $query -> execute();
        $res = $query -> get_result();
        $query -> close();
    }else{
        echo json_encode(response(300,sqlError($sql,"i",$Id_producto)));
    }
    $preguntas = array();
    while($row = $res -> fetch_Assoc()){
        $row['fecha'] = get_diferencia($row['fecha']);
        $sql2 = "SELECT * 
                FROM respuestas 
                WHERE Id_pregunta = ?";
        if($query = $enlace -> prepare($sql2)){
            $query -> bind_param("i",$row['Id_pregunta']);
            $query -> execute();
            $res2 = $query -> get_result();
            $query -> close();
        }else{
            echo json_encode(response(300,sqlError($sql,"i",$row['Id_pregunta'])));
        }
        $row['respuestas'] = array();
        while($row2 = $res2->fetch_Assoc()){
            array_push($row['respuestas'],$row2);
        }
        array_push($preguntas,$row);
    }
    if($preguntas != []){
        echo json_encode(response(200,$preguntas));
    }else{
        echo json_encode(response(305));
    }
    
    return;
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
?>

