<?PHP
    header("Content-Type:application/json");
    include("funciones.php");
    $acceptedMethods = array("GET","POST");
    $method = getMethod(); 
    $log  = getLogger("Localizacion");
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
        default:
            $res = response(500,methodError($acceptedMethods,$method));
    }

    echo json_encode($res);

    function get($req){
        global $db;
        global $log;
        $required = array("NotNull" => array("latitud","longitud"));
        $types_ref = array("latitud"=>"d","longitud"=>"d");
        $res = paramsRequired($req,$required);
        if($res['status_code']!=200){
            return $res;
        }
        $params = $req;
        $types = getTypes($params,$types_ref);
        $sql="SELECT * FROM localizaciones WHERE latitud = ? AND longitud = ?";
        if($query = $db -> prepare($sql)){
            $query -> bind_param($types,$req['latitud'],$req['longitud']);
            $query -> execute();
            $res = $query -> get_result();
            $query -> close();
        }else{
            $log -> error("Ocurrio un error al realizar la consulta " . sqlError($sql, $types, $params));
            return response(300);
        }
        $row = $res -> fetch_Assoc();
        return response(200,$row);
    }
    function post($req){
        global $db;
        global $log;
        
        $required = array("NotNull" => array("latitud","longitud"),"Null"=>array("estado","ciudad","pais"));
        $types_ref = array("estado"=>"s","ciudad"=>"s","pais"=>"s","latitud"=>"d","longitud"=>"d");
        $res = paramsRequired($req,$required);
        if($res['status_code']!=200){
            return $res;
        }
        $params = $req;
        $res = dbInsert("localizaciones",getTypes($params,$types_ref),$params);
        return $res;
    }