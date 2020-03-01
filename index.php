<?php
session_start();
include_once("../core/connection.php");
include_once("../core/site_build.php");
if (!empty($_REQUEST["rdir"])){
    $message        = "Email or Password Blank";
}else{
    $message        = "";
}
$app_links = [];
if(isset($_SESSION["is_auth"])){
    if($_SESSION['check']!== sha1($_SERVER['HTTP_USER_AGENT'] . "Fearisthekiller")){
        unset($_SESSION['is_auth']);
        session_destroy();
        header("location: ../core/index.php?error=sh");
    }
    $get_tools_access = "SELECT t.Tool_Name, t.Tool_URL, t.Access_Level
FROM terminus.tools t
	JOIN terminus.tool_access ta
		ON t.Tool_ID = ta.Tool_ID
WHERE ta.User_ID = ?";
    $id = $_SESSION['User_Id'];
    $query  = $db->prepare($get_tools_access);
    $query->bind_param('i',$id);
    $query->execute();
    $query->store_result();
    $query->bind_result($Tools_Name,$Tool_URL,$Access_Lvl);
    $app_links  = [];
    $count  = 0;
    while($query->fetch()){
        $app_links[$count]=["tname"=>"$Tools_Name","turl"=>"$Tool_URL","alvl"=>"$Access_Lvl"];
        $count++;
    }
}



$Builder = new siteBuild();
$header = $Builder->headerBuild($app_links);

$count_tools        = count($app_links);
echo "$count_tools<br>";

$start_tool_cnt      = 0;

while($start_tool_cnt < $count_tools){
    echo "link {$app_links[$start_tool_cnt]['turl']} Name is {$app_links[$start_tool_cnt]['tname']} <br>";

    $start_tool_cnt++;
}

var_dump($app_links);

$absolute_url = $Builder->full_url($_SERVER);
if(isset($_SESSION["is_auth"])) {
    if (in_array('Meal', $apps_list)) {
        include_once("../body/class.Body.php");
        $body = new Adonis();
        $record_bw = $body->recordMeal($id, $absolute_url);

        $Weight_array = $body->GetgraphWeight($id);
        $Graph = new chartBuild();
        $Graph_BW = $Graph->simpleChart($Weight_array);

    }
}



/*

$Tools_prep    = $db->prepare($get_tools_access);
$Tools_prep->execute();
$Tools_return  = $Tools_prep->fetch();
*/



//var_dump($Tools_return);
//echo $header;
//include_once ("header.php");
$Builder->footerBuild($app_links);
?>
