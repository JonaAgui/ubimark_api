<?PHP
    header("Content-Type:application/json");
    include("funciones.php");
    $acceptedMethods = array("GET");
    $method = getMethod(); 
    $log  = getLogger("Resumen");
    $db = getDBConnection();

    switch($method){
        case "GET":
            $log->trace("GET");
            $req = $_GET;
            $res = get($req);
            break;
        default:
            $res = response(500,methodError($acceptedMethods,$method));
    }
    $log->trace("Fin");
    $db -> close();
    echo json_encode($res);

    function get($req){
        global $log;
        global $db;
        $res = array();
        $id = $_COOKIE['Id'];
        $sql = "SELECT count(*) FROM productos WHERE Id_usuario = ? AND tipo_cuenta = 'PERSONAL'";
        if($query = $db -> prepare($sql)){
            $query -> bind_param("i",$id);
            $query -> execute();
            $query -> bind_result($prods);
            $query -> fetch();
            $query -> close();
        }else{
            return response(300,sqlError($sql,"i",$id));
        }
        $res['prods'] = $prods;
        $sql = "SELECT trabaja_en FROM usuario WHERE Id_usuario = ?";
        if($query = $db -> prepare($sql)){
            $query -> bind_param("i",$id);
            $query -> execute();
            $query -> bind_result($empresa);
            $query -> fetch();
            $query -> close();
        }else{
            return response(300,sqlError($sql,"i",$id));
        }
        if(isset($empresa) && $empresa != null){
            $sql = "SELECT count(*) FROM productos WHERE Id_usuario = ? AND Id_empresa = ?";
            if($query = $db -> prepare($sql)){
                $query -> bind_param("ii",$id,$empresa);
                $query -> execute();
                $query -> bind_result($prods_emp);
                $query -> fetch();
                $query -> close();
            }else{
                return response(300,sqlError($sql,"ii",array("Id_usuario" => $id, "Id_empresa" => $empresa)));
            }
        }
        $res['prods_empresa'] = $prods_emp;
        return $res;
    }