<?PHP
    header("Content-type:application/json");
    include("funciones.php");
    $notificaciones = $_POST['notificaciones'];
    $estado = array("estado" => $_POST['estado']);
    foreach ($notificaciones as $notificacion) {
        $res = dbUpdate("notificaciones","s",$estado,"i",array("Id_notificacion"=>$notificacion));
    }
    echo json_encode(response(200));
?>