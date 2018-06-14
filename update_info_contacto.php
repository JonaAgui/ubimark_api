<?php
    header("Content-Type:application/json");
    include_once("db/conectar.php");
    include("funciones.php");
    $table = "usuario";
    $params = $_POST;
    $types = "";
    $target['Id_usuario'] = $_COOKIE['Id'];
    $target_types = "i";
    $values = array();
    //Comprueba que no haya valores nulos y busca los tipos
    foreach($params as $key => $val){
        if($key != 'numinterior' && $key != 'correo_back'){
            if(!notNull($val)){
                echo json_encode(response(302,$key));
                return;
            } 
        }
        if($key == 'cp' or $key == 'numero' or $key == 'telefono'){
            $types .= "i";
        }else{
            $types .= "s";
        }
    }
    //Inserta los datos
    $result = dbUpdate($table,$types,$params,$target_types,$target);
    echo json_encode($result);
?>
