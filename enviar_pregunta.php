<?PHP 
    header("Content-Type:application/json");
    include_once("db/conectar.php");
    include_once("funciones.php");
    $params = $_POST;
    $params['Id_cliente'] = $_COOKIE['Id'];
    $date = new \DateTime('now', new \DateTimeZone('America/Mexico_City'));
    $actual = time();
    $actual = $date->format("Y-m-d H:i:s");
    $params['fecha'] = $actual;
    $sql = "SELECT * 
            FROM productos 
            WHERE Id_producto = ?";
    if ($query = $enlace -> prepare($sql)){
        $query -> bind_param("i",$params['Id_producto']);
        $query -> execute();
        $res = $query -> get_result();
        $query -> close();
    }else{
        echo json_encode(response(300,sqlError($sql,"i",$params['Id_producto'])));
        return;
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
        echo json_encode($result);
        return;
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
        echo json_encode($result2);
        return;
    }
   
    $respuesta = array("user"=> $params['Id_cliente'], "pregunta" => $params['pregunta'], "target" => $result['data']['ID'], 
            "fecha" =>$params['fecha'],"destino"=>$Id_vendedor,"tipo"=>$tipo_vendedor);
    echo json_encode(response(200,$respuesta));
    
?>