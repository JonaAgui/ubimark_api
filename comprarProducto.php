<?PHP
    header("Content-Type:application/json");
    include_once("db/conectar.php");
    include("funciones.php");
    $sess = check_session();
    if($sess['status_code'] != 101){
        echo json_encode(response(100));
        return;
    }
    $Id = $_COOKIE['Id'];
    $Id_prod = $_POST['Id_prod'];
    $table = "pedido";
    $types = "iiidsss";
    $sql = "SELECT p.precio,p.existencias,p.nombre_producto FROM productos p  WHERE p.Id_producto = ?";
    if($query = $enlace -> prepare($sql)){
        $query->bind_param("i",$Id_prod);
        $query -> execute();
        $query -> bind_result($precio,$existencias,$nombre);
        $query -> fetch();
        $query -> close();
    }else{
        echo json_encode(response(300,sqlError($sql,"i",$Id_prod)));
        return;
    }
    if($existencias<1){
        echo json_encode(response(304,array("nombre_producto"=>$nombre)));
        return;
    }
    
    $params = array(); 
    $params['Id_usuario'] = $Id;
    $params['Id_producto'] = $Id_prod;
    $params['cantidad'] = "1";
    $params['total'] = $precio;
    $params['fecha'] = date("y-m-d");
    $params['estado'] = "Envio";
    $params['fecha_entrega'] = "00-00-00";
    $result = dbInsert($table,$types,$params);
    if($result['status_code'] == 200){
        $result = dbUpdate("productos","i",array("existencias" => $existencias-1),"i",array("Id_producto"=>$Id_prod));
        if($result['status_code'] != 200){
            echo json_encode($result);
            return;  
        }
    } else{
        echo json_encode($result);
        return;
    }
    
    echo json_encode(response(200));
?>