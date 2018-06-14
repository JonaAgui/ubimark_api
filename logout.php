<?PHP 
    header("Content-type:application/json");
    include("db/conectar.php");
    include("funciones.php");
    setcookie('Activo',"True",time()-3600,'','','',TRUE);
    setcookie('Id','',time()-3600,'','','',TRUE);
    setcookie('Pass','',time()-3600,'','','',TRUE);
    $enlace->close();
    echo json_encode(response(200));

?>