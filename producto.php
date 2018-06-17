<?php
    header("Content-Type:Application/json");
    include("funciones.php");
    
    $acceptedMethods = array("GET");
    $method = getMethod(); 
    $log  = getLogger("Producto");
    $db = getDBConnection();
    switch($method){
        case "GET":
            $log->trace("GET");
            $req = $_GET;
            $res = get($req);
            break;
        case "DELETE":
            $log->trace("DELETE");
            parse_str(file_get_contents("php://input"),$req);
            $req = getRequest($req,file_get_contents("php://input"));
            $res = delete($req); 
            break;
        default:
            $res = response(500,methodError($acceptedMethods,$method));
    }

    echo json_encode($res);
    function get($req){
        global $log;
        if(isset($req['id_producto'])){
            $log->trace("Por id producto");
            return getXIdProd($req);
        }else if(isset($req['tipo'])){
            switch($req['tipo']){
                case "PERSONAL":
                    $log->trace("Por vendedor particular");
                    return getXUser($req);
                    break;
                case "EMPRESA":
                    $log->trace("Por vendedor empresa");
                    return getXEmpresa($req);
                    break;
            }
            
        }
    }
    function getXIdProd($req){
        global $db;
        global $log;
        $id = $req['id_producto'];
        $log->info("Buscando producto...");
        $sql = "SELECT p.* FROM productos p WHERE p.Id_producto = ? AND estado = 'AC'";
        if($query = $db->prepare($sql)){
            $query->bind_param("i",$id);
            $query->execute();
            $res = $query->get_result();
            $query->close();
        }else {
            return response(300, array('sql' => $sql, "params" => $id, ));    
        }
        $row = $res->fetch_Assoc(); 
        $log -> info("Buscando imagenes del producto...");
        $sql = "SELECT path, Id_usuario as uploadedBy FROM imagen_prod WHERE Id_producto = ?"; 
        if($query = $db->prepare($sql)){
            $query->bind_param("i",$row['Id_producto']);
            $query->execute();
            $res = $query->get_result();
            $query->close();
        }else {
            return response(300, array('sql' => $sql, "params" => $row['Id_producto'], ));    
        }
        $row['images']=array();
        while($image = $res-> fetch_Assoc()){
            array_push($row['images'],$image);
        }
        $log->info("Buscando vendedor");
        if($row['tipo_cuenta']=="EMPRESA"){
            $sql = "SELECT nombre_empresa AS nombre, coordenadas, CONCAT(calle,' ',numero,' ',numinterior,'|','Colonia ' ,colonia, ' C.P. ',cp,'|',ciudad,' ',estado) AS addr, calificacion
                    FROM empresa WHERE Id_empresa = ?";
            if($query = $db->prepare($sql)){
                $query->bind_param("i",$row['Id_empresa']);
                $query->execute();
                $res = $query->get_result();
                $query->close();
            }else {
                return response(300, array('sql' => $sql, "params" => $id, ));   
            }
        }else{
            $sql = "SELECT CONCAT(nombre , ' ', apellidos) AS nombre, CONCAT(delegacion,' ',estado) AS addr, calificacion
                    FROM usuario WHERE Id_usuario = ?";
            if($query = $db->prepare($sql)){
                $query->bind_param("i",$row['Id_usuario']);
                $query->execute();
                $res = $query->get_result();
                $query->close();
            }else {
                return response(300, array('sql' => $sql, "params" => $id, ));
            }   
        }
        $row['vendedor'] = $res -> fetch_Assoc();
        $row['vendedor']['addrs'] = preg_split("[\|]",$row['vendedor']['addr']);
        unset($row['vendedor']['addr']);
        return response(200,$row);
    }

    function getXUser($req){
        global $log;
        global $db;
        $Id = $_COOKIE['Id'];
        $sql = "SELECT * 
                FROM productos p  
                WHERE Id_usuario = ? AND tipo_cuenta = 'PERSONAL' AND estado = 'AC'";
        if($query =$db -> prepare($sql)){
            $query->bind_param("i",$Id);
            $query->execute();
            $res = $query->get_result();
            $query->close();
        }else{
            return response(300,sqlError($sql,"i",$Id));
            
        }
        $result=array();
        while($row = $res->fetch_Assoc()){
            $sql = "SELECT path, Id_usuario 
                    FROM imagen_prod 
                    WHERE Id_producto = ? 
                    LIMIT 1";
            if($query=$db->prepare($sql)){
                $query->bind_param("i",$row['Id_producto']);
                $query->execute();
                $query->bind_result($path,$id_usuario);
                $query->fetch();
                $query->close();
            }else{
                return response(300,sqlError($sql,"i",$Id));
            }
            $row['imagen']=array("path" => $path,"Id_usuario" => $id_usuario);
            array_push($result,$row);
        }
        return response(200,$result);
    }

    function getXEmpresa($req){

    }


    function delete($req){
        global $log;
        $log->trace($req);
        return dbUpdate("productos","s",array("estado"=>"IN"),"i",$req);  
    }