<?PHP
    header("Content-Type:application/json");
    include_once("funciones.php");
    $JSON = json_decode(file_get_contents("php://input"), true);
    if(isset($JSON) && $JSON != null){
        $_POST = $JSON;
    }
    $params = $_POST;
    
    $date = new \DateTime('now', new \DateTimeZone('America/Mexico_City'));
    $actual = time();
    $actual = $date->format("Y-m-d H:i:s");
    $params['fecha'] = $actual;
    $result = dbInsert("respuestas","iss",$params);
    $result['data']['fecha'] = $actual;
    echo json_encode($result);



    
?>