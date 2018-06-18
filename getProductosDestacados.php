<?PHP 
    header("Content-Type:application/json");
    include_once("db/conectar.php");
    include("funciones.php");
    
    $sql = "SELECT `busqueda`,COUNT(*) AS n FROM `busquedas`";
    if(isset($_POST['estado'])){
        $estado = $_POST['estado'];
        $sql .= " WHERE `estado` LIKE ?";
    }
    
    $sql .= " GROUP BY `busqueda` ORDER BY n DESC LIMIT 6 ";
    if ($query = $enlace->prepare($sql)){
        if(isset($_POST['estado'])){
            $query -> bind_param("s",$estado);
        }
        $query -> execute();
        $res = $query->get_result();
        $query -> close();
    }else{
        echo json_encode(response(300,sqlError($sql,"s",$estado)));
        return;
    }
    $results = array();
    $busqueda = "";
    while($row = $res-> fetch_Assoc()){
        $busqueda.=$row['busqueda']." ";
        
    }

    $result = buscar(trim($busqueda));
    $max = 6;
    if(count($results) < 6){
        $max = count($result);
    }
    for($i = 0;$i < $max;$i++){
        $val = $result[$i];
        $sql = "SELECT P.*, GROUP_CONCAT(T.tag) AS tags FROM productos P INNER JOIN map_tag M ON M.ID_producto = P.Id_producto INNER JOIN tags T ON T.ID_tag = M.ID_tag WHERE P.Id_producto = ?";
        if($query = $enlace->prepare($sql)){
            $query -> bind_param("i",$val);
            $query -> execute();
            $res = $query -> get_result();
            $query -> close();
        }else{
            echo json_encode(response(300,sqlError($sql,"i",$val)));
            return;
        }
        $sql = "SELECT path, Id_usuario FROM imagen_prod WHERE Id_producto = ? LIMIT 1";
        if($query = $enlace->prepare($sql)){
            $query -> bind_param("i",$val);
            $query -> execute();
            $query -> bind_result($img,$id_usuario);
            $query -> fetch();
            $query -> close();
        }else{
            echo json_encode(response(300,sqlError($sql,"i",$val)));
            return;
        }
        
        while($row = $res->fetch_Assoc()){ 
            $row['path'] = $img;
            $row['author'] = $id_usuario;
            array_push($results,$row);
        }
    }
    echo json_encode(response(200,$results));


?>