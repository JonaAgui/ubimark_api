<?PHP
    header("Content-Type:application/json");
    include("funciones.php");
    $acceptedMethods = array("GET");
    $method = getMethod(); 
    $log  = getLogger("Buscador");
    $db = getDBConnection();
    switch($method){
        case "GET":
            $log ->trace("GET");
            $req = $_GET;
            $res = get($req);
            break;
        default:
            $res = response(500,methodError($acceptedMethods,$method));
    }

    echo json_encode($res);

    function get($req){
        global $log;
        global $db;
        $token = $req['search'];
        $results = buscar($token);
        $result = array();
        foreach($results as &$val){
            $sql = "SELECT P.* FROM productos P WHERE P.Id_producto=? AND estado = 'AC'";
            if($query=$db->prepare($sql)){
                $query->bind_param("i",$val);
                $query->execute();
                $res=$query->get_result();
                $query->close();
            }else{
                return response(300,"i",$val);
            }
            while($row = $res-> fetch_Assoc()){
                $sql = "SELECT path, Id_usuario AS uploader FROM imagen_prod WHERE Id_producto = ? LIMIT 1";
                if($query=$db->prepare($sql)){
                    $query->bind_param("i",$val);
                    $query->execute();
                    $res2 = $query -> get_result();
                    $query->close();
                }else{
                    return response(300,sqlError($sql,"i",$val));
                }
                $row['image'] = $res2->fetch_Assoc();
                $result[$val] = $row;
            }
        }

        return response(200,$result);

    }

    
?>