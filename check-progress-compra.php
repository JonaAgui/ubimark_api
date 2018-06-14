<?php
    header("Content-type:application/json");

    include("db/conectar.php");
    include_once("funciones.php");

    $id_usuario = $_COOKIE['Id'];
    $band1 = $band2 = false; //band1 info personal band2 info de contacto
    $datos = array();
    $sql = "SELECT * FROM usuario WHERE Id_usuario = ?";
    //Datos a checar
    $datosPersonales=array("nacimiento","sexo");
    $datosContacto=array("telefono","estado","delegacion","colonia","cp","calle","numero");
    

    //Busca los datos del usuario
    if($query = $enlace->prepare($sql)){
        $query->bind_param("i", $id_usuario);
        $query->execute();
        $res = $query->get_result();
        while($row = $res->fetch_assoc()){
            $datos=$row;
        }
        $query->close();
    }else{
        echo json_encode(response(300, array("sql" => $sql, "params" => array("i", $id_usuario))));
    }
    $band1 = checkDatos($datos,$datosPersonales);
    $band2 = checkDatos($datos,$datosContacto);

    if($band1 and $band2){
        echo json_encode(response(200,"Completed3"));
    }else if($band2){
        echo json_encode(response(201,"Completed2"));
    }else if($band1){
        echo json_encode(response(201,"Completed1"));
    }else{
        echo json_encode(response(201,"Completed0"));
    }
    
    /**
     * Función que checa si los datos especificados no son nulos
     *
     * @param array $datos
     * @param array $datos2check
     * @return boolean 
     */
    function checkDatos($datos,$datos2check){
        foreach ($datos2check as $dato){
            if(!notNull($datos[$dato])){
                return false;
            }
        }
        return true;
    }
?>