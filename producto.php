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
        default:
            $res = response(500,methodError($acceptedMethods,$method));
    }

    echo json_encode($res);

    function get($req){
        global $db;
        global $log;
        $id = $req['key'];
        $log->info("Buscando producto...");
        $sql = "SELECT p.* FROM productos p WHERE p.Id_producto = ?";
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