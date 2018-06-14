<?php
    header("Content-Type:application/json");
    include("conectar.php");
    include("funciones.php");
    //Comprueba que no haya campos nulos en la petición
    foreach ($_POST as $key => $value) {
        if(!notNull($value)){
            echo json_encode(302,$key);
        }   
    }
    //Obtiene lo datos
    $params = $_POST;
    $params['venta_activada'] = "N";
    $types = "sssss";
    $table = "usuario";
    
    //Busca que el correo no se encuentre registrado
    if($query = $enlace->prepare("SELECT count(*) FROM usuario WHERE correo = ?")){
        $query->bind_param("s",$params['correo']);
        $query->execute();
        $query->bind_result($count);
        $query->fetch();
        $query->close();
    }
    //Si no hay una cuenta registrada crea una nueva
    if($count < 1){
        $params['contrasena'] = password_hash($params['contrasena'],PASSWORD_DEFAULT);
        $result = dbInsert($table,$types,$params);
        echo json_encode($result);
    }else{
        echo json_encode(response(102));
    }
    
?>
