<?PHP
    header("Content-type:application/json");
    include_once("db/conectar.php");
    include("funciones.php");

    $params = $_POST;
    $params['estado'] = "NO_LEIDO";

    $types = "siiisss";

    $result = dbInsert("notificaciones",$types,$params);
    if($result['status_code']!=200){
        echo json_encode($result);
        return;
    }else{
        $params['Id_notificacion'] = $result['data']['ID'];
    }

    
    echo json_encode(response(200,$params));
?>