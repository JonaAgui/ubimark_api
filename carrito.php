<?PHP
    header("Content-Type:application/json");
    include("funciones.php");
    $acceptedMethods = array("GET","POST","PUT","DELETE");
    $method = getMethod(); 
    $log  = getLogger("Carrito");
    $db = getDBConnection();

    switch($method){
        case "GET":
            $res = get();
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

    function get(){
        global $log;
        global $db;
        $id = $_COOKIE['Id'];
        $sql = "SELECT c.*,p.nombre_producto,p.precio,p.existencias,i.path,i.Id_usuario AS vendedor 
                FROM carrito c 
                JOIN productos p ON p.Id_producto = c.Id_producto 
                JOIN imagen_prod i ON i.Id_producto=p.Id_producto 
                WHERE c.Id_usuario = ? GROUP BY p.Id_producto";
        if($query = $db->prepare($sql)){
            $query->bind_param("i",$id);
            $query->execute();
            $res = $query->get_result();
            $query->close();
        }else{
            return response(300,sqlError($sql,"i",$id));
        }
        $result=array();
        while($row = $res -> fetch_assoc()){
            array_push($result,$row);
        }
        return response(200,$result);
    }

    function post($req){
        global $log;
        global $db; 
        $required = array("NotNull" => array("Id_producto", "cantidad"));
        $res = paramsRequired($req,$required);
        if($res['status_code']!=200){
            return $res;
        }
        
        $table = "carrito";
        $params = $req;
        $params["Id_usuario"] = $_COOKIE['Id'];
        $types = "iii";
        $log -> trace("Searching for item already in cart");
        $sql = "SELECT count(*) 
                FROM carrito 
                WHERE Id_producto = ? AND Id_usuario = ?";
        if($query = $db -> prepare($sql)){
            $query->bind_param("ii",$params['Id_producto'],$params['Id_usuario']);
            $query->execute();
            $query->bind_result($count);
            $query->fetch();
            $query->close();
        }else{
            $log -> warn("Item already in cart");
            return response(300,sqlError($sql,"ii",array($params['Id_producto'],$params['Id_usuario'])));
        }

        if($count >= 1){
            return response(303, array($params,"en_carrito" => $count));
        }
        $log -> trace("Insert item in cart");
        $result =  dbInsert($table, $types, $params);
        $log->info("POST DONE!");
        return $result;
    }

    function put($req){
        global $log;
        global $db;
        $required = array("NotNull" => array("folio_carrito", "cantidad"));
        $res = paramsRequired($req,$required);
        if($res['status_code']!=200){
            return $res;
        }
        $folio = array("folio_carrito" => $req['folio_carrito']);
        $cantidad = array("cantidad" => $req['cantidad']);
        $table = "carrito";
        $log->trace("Updating cart item");
        $result = dbUpdate($table,"i",$cantidad,"i",$folio);
        if($result["status_code"] != 200){
            return $result;
        }
        $log->trace("Recovering new data");
        $sql = "SELECT c.*,p.nombre_producto,p.existencias,p.precio 
                FROM carrito c 
                JOIN productos p ON p.Id_producto = c.Id_producto 
                WHERE c.folio_carrito = ? ";
        if($query = $db->prepare($sql)){
            $query->bind_param("i",$folio['folio_carrito']);
            $query->execute();
            $res = $query->get_result();
            $query->close();
        }else{
            return response(300,sqlError($sql,"i",$id));
        }
        $result=array();
        while($row = $res -> fetch_assoc()){
            array_push($result,$row);
        }
        $log->info("PUT DONE!");
        return response(200,$result);
    }

    function delete($req){
        global $log;

        $required = array("NotNull" => array("folio_carrito"));
        $res = paramsRequired($req,$required);
        if($res['status_code']!=200){
            return $res;
        }
        $params = $req;
        $types = "i";
        $table = "carrito";
        $log->trace("Delete item");
        $result = dbDelete($table,$types,$params); 
        $log->info("DELETE DONE!");
        return $result;
    }