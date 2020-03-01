<?php
session_start();
include_once("../core/connection.php");
include_once("../core/site_build.php");
include_once("../core/chart/simple_chart.php");
if (!empty($_REQUEST["rdir"])){
    $message        = "Email or Password Blank";
}else{
    $message        = "";
}
$Builder = new siteBuild();
$app_links = [];
if(isset($_SESSION["is_auth"])) {
    if ($_SESSION['check'] !== sha1($_SERVER['HTTP_USER_AGENT'] . "Fearisthekiller")) {
        unset($_SESSION['is_auth']);
        session_destroy();
        header("location: ../core/index.php?error=sh");
    }
    $id = $_SESSION['User_Id'];
    $apps_list_f = $Builder->app_list($id);

    $app_a_count    = count($apps_list_f);
    $app_start = 0;
    $app_links = $apps_list_f;
    $apps_list = [];
    while($app_start<$app_a_count){
        $apps_list[] = $apps_list_f[$app_start]['tname'];
        $app_start++;
    }
}else{
    $Builder->login_form();
    die();
}

$header = $Builder->headerBuild($app_links);
$absolute_url = $Builder->full_url($_SERVER);
if(isset($_SESSION["is_auth"])) {
    if (in_array('Adonis', $apps_list)) {
        include_once("../body/class.Body.php");
        $body = new Adonis();
  
        $track_bw = $body->trackWeight($id,$absolute_url);

        echo "</row></div>";
    }


}


$Builder->footerBuild($app_links);


?>
