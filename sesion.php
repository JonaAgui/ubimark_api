<?PHP
    header("Content-Type:application/json");
    include("funciones.php");
    $acceptedMethods = array("GET","POST","PUT","DELETE");
    $method = getMethod(); 
    $log  = getLogger("sesion");
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
        case "DELETE":
            $log -> trace("DELETE");
            parse_str(file_get_contents("php://input"),$req);
            $req = getRequest($req,file_get_contents("php://input"));
            $res = delete($req); 
            break;
        default:
            $res = response(500,methodError($acceptedMethods,$method));
    }

    echo json_encode($res);

    function get($req){
        global $db;
        global $log;
    }
    function post($req){
        global $db;
        global $log;
    }
    function put($req){
        global $db;
        global $log;
    }
    function delete($req){
        global $db;
        global $log;
        $params = array("Id_usuario" => $_COOKIE['Id'],"token"=>$_COOKIE['token']);
        setcookie('Activo',"True",time()-3600,'','','',TRUE);
        setcookie('Id','',time()-3600,'','','',TRUE);
        setcookie('token','',time()-3600,'','','',TRUE);
        setcookie('empresa','',time()-3600,'','','',TRUE);
        dbDelete("sesiones_activas","is",$params);
        $db->close();
        return response(200);
    }