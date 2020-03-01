<?php
class Adonis
{
    public function recordWeight($user,$url)
    {
        echo "
  <link rel=\"stylesheet\" href=\"../core/scripts/datepick/jquery-ui.css\">
  <link rel=\"stylesheet\" href=\"../core/scripts/datepick/style.css\">
  <script src=\"../core/scripts/datepick/jquery-1.12.4.js\"></script>
    <script src=\"../core/scripts/datepick/jquery-ui.js\"></script>
  <script>
  $( function() {
    $( \"#datepicker\" ).datepicker({   dateFormat: \"yy-mm-dd\" });
  } );
  </script>";
        $today  = date('Y-m-d');
        echo "  <div class=\"row\">
    <div class=\"col-7\">
     Enter Weight<br>
    <form style=\"width: 60%\" action=\"../core/scripts/php/update.php\" method=\"post\">
  <div class=\"form-group\">
    <input type=\"text\" class=\"form-control\" name=\"bw\" placeholder=\"Body Weight\">
  </div>
  <div class=\"form-group\">
    <input type=\"text\" id=\"datepicker\" class=\"form-control\" name=\"date\" value=\"$today\">
    <input type=\"hidden\" id=\"return_url\" name=\"return_url\" value=\"$url\">
    <input type=\"hidden\" id=\"user_id\" name=\"user\" value=\"$user\">
    <input type=\"hidden\" id=\"action\" name=\"action\" value=\"record_bw\">
  </div>
  <button type=\"submit\" class=\"btn btn-primary\">Submit</button>
</form></div>";

    }
    public function trackWeight($user,$url)
    {
        $url = '../../index.php';
        echo "<div class=\"col-4\">
  <link rel=\"stylesheet\" href=\"../core/scripts/datepick/jquery-ui.css\">
  <link rel=\"stylesheet\" href=\"../core/scripts/datepick/style.css\">
  <script src=\"../core/scripts/datepick/jquery-1.12.4.js\"></script>
    <script src=\"../core/scripts/datepick/jquery-ui.js\"></script>
  <script>
  $( function() {
    $( \"#datepicker1\" ).datepicker({   dateFormat: \"yy-mm-dd\" });
  } );
      $( function() {
    $( \"#datepicker2\" ).datepicker({   dateFormat: \"yy-mm-dd\" });
  } );
  </script>";
        $today  = date('Y-m-d');
        echo "Enter Goal Tracker<br>
    <form style=\"width: 60%\" action=\"../core/scripts/php/update.php\" method=\"post\">
  <div class=\"form-group\">
    <input type=\"text\" class=\"form-control\" name=\"goal_title\" placeholder=\"Goal Title\">
    <input type=\"text\" class=\"form-control\" name=\"goal_bw\" placeholder=\"Goal Weight\">
  </div>
  <div class=\"form-group\">
    <input type=\"text\" id=\"datepicker1\" class=\"form-control\" name=\"goal_start\" value=\"Goal Start\">
    <input type=\"text\" id=\"datepicker2\" class=\"form-control\" name=\"goal_end\" value=\"Goal End\">
    <input type=\"hidden\" id=\"return_url\" name=\"return_url\" value=\"$url\">
    <input type=\"hidden\" id=\"user_id\" name=\"user\" value=\"$user\">
    <input type=\"hidden\" id=\"action\" name=\"action\" value=\"track_bw\">
  </div>
  <button type=\"submit\" class=\"btn btn-primary\">Submit</button>
</form></div>";

    }
    public function trackDisplay($user,$url)
{
    $db = $this->conn();
    $Get_Data = "SELECT a.Goal_BW,
a.Goal_Title,
b.BW,
DATEDIFF(a.Goal_End,CURDATE()) AS Days_To_Go, 
a.Goal_End,
b.BW-a.Goal_BW AS Weight_to_Go,
a.Start_BW,
a.Start_BW-b.BW AS Lost_So_Far,
(a.Start_BW-b.BW)/DATEDIFF(CURDATE(),a.Goal_Start) AS Lost_So_Far_Per_Day
FROM self.Weight_Tracking a
	JOIN self.Adonis b
	ON a.User_ID = b.User_ID
	AND b.ID = (SELECT MAX(ID) FROM self.Adonis WHERE User_ID = $user)
WHERE a.User_ID = $user
AND CURDATE() <= a.Goal_End
";
    $query = $db->prepare($Get_Data);
    //$query->bind_param('i', $user);
    $query->execute();
    $query->store_result();
    $query->bind_result($Goal_BW, $Goal_Title,$Cur_BW,$Days_To_Go,$Goal_EndDate,$Weight_to_Go,$Start_BW,$Lost_so_Far,$Lost_so_Far_per_Day);


    echo "Goals<br><p style='font-size:12px;'>To enter Goal/s <a href=\"../body/goals.php\">click here</a></p>";
    while ($query->fetch()) {
        $Lose_Gain = $Lost_so_Far >= 1 ? 'lost':'gained';
        echo "<b>$Goal_Title</b> is here in <u>$Days_To_Go</u> days.  You have <u>$Weight_to_Go</u> to go to get to your goal, and currently have $Lose_Gain $Lost_so_Far at a average of $Lost_so_Far_per_Day per day.";
        echo "<br>";
    }

}
    public function mealDisplay($user,$url)
    {
        Echo "<br><b>30 Day Meal</b><br>";
        $db = $this->conn();
        $Get_Data = "SELECT
  a.Meal_Date,
  b.Catagory,
  c.Meal_Type,
   CASE a.Meal_Type
    WHEN  5 THEN 0
    WHEN 6 THEN 0
    ELSE 1
   END AS Meal_Count
FROM
  self.Meals a
  JOIN self.Meal_Rate b
    ON a.Catagory = b.id
  JOIN self.Meal_Types c
    ON a.Meal_type = c.Meal_ID
WHERE a.User_ID = 1
  AND a.Meal_Date > DATE_SUB(CURDATE(), INTERVAL 30 DAY)
ORDER BY a.Meal_Date ASC
";
        $query = $db->prepare($Get_Data);
        //$query->bind_param('i', $user);
        $query->execute();
        $query->store_result();
        $query->bind_result($Meal_Date, $Catagory,$Meal_Type,$Meal_Count);
    $cur_good_streak = 0;
    $longest_good_streak = 0;
    $total_meals        = 0;
    $total_good_meals   = 0;
    $current            = 0;
    $fast_start         = [];
    $fast_end           = [];
    $fast_length        = [];
    $fasting_track      = 0;
    $count_of_return = $query->num_rows;
        while ($query->fetch()) {
            $cur_meal_time  = new DateTime($Meal_Date);
            if (($Catagory == 'Good' || $Catagory == 'Low/0 Carb' )&& $Meal_Count == 1){
                $cur_good_streak++;
                $longest_good_streak++;
                $total_good_meals++;
            }elseif($Catagory == 'Bad'){
                $longest_good_streak = 0;
                $cur_good_streak = 0;
            }
            if ($count_of_return == ($current+1) && $Meal_Type == 'Start Fast'){
                $now                = date('Y-m-d H:i:s');
                $now                = new DateTime($now);
                //$fast_time_cur      = $cur_meal_time->diff($now)->format("%h:%I:%S");
                $fast_time_cur_h      = floor(($now->getTimestamp() - $cur_meal_time->getTimestamp()) / 3600);
                $fast_time_cur_m      = floor(($now->getTimestamp() - $cur_meal_time->getTimestamp()) / 60 % 60);
                $fast_time_cur_s      = floor(($now->getTimestamp() - $cur_meal_time->getTimestamp()) % 60);
            }
            if ($Meal_Type == 'Start Fast'){
                //echo "<br>Fast start " . $cur_meal_time->format('Y-m-d H:i:s') . "<br>";
                $fast_start[] = $cur_meal_time;
                $fasting_track = $current;
            }
            if($current == ($fasting_track+1) && $fasting_track > 0){
                //echo "<br>Fast end " . $cur_meal_time->format('Y-m-d H:i:s') . "<br>";
                $fast_end[] = $cur_meal_time;
            }
            $current++;
            $total_meals = $total_meals + $Meal_Count;
        }
        $per_good_meals = number_format((($total_good_meals/$total_meals)*100),2);
        echo "In the Last 30 days you had $total_good_meals good meals of $total_meals. You ate well $per_good_meals% of the time";
        ## Removed till I get the streaks working "Your last $cur_good_streak have been good and the longest streak of good meals is $longest_good_streak";
        for ($i = 0; $i < count($fast_end); $i++)  {
            $fast_length[]  = $fast_end[$i]->getTimestamp() - $fast_start[$i]->getTimestamp();
        }
        if(isset($fast_time_cur_h)) {
            echo "<br><br><b><u>Fasting</u></b><br>You currently have been fasting for {$fast_time_cur_h}h:{$fast_time_cur_m}m:{$fast_time_cur_s}s";
        }
        if (count($fast_length)>0){
            arsort($fast_length);
            $fast_h = floor($fast_length[0]/3600);
            $fast_m = floor($fast_length[0] / 60 % 60);
            echo "<br>You Fasted " . count($fast_length) . " times in the last 30 days. The longest was $fast_h:$fast_m";
        }
    }
    public function GetgraphWeight($user,$date_num = null,$date_unit = null){

    $db = $this->conn();
        if ($date_num == null){
            $date_num = 3;
        }
        if($date_unit == null){
            $date_unit = 'MONTH';
        }
        $sql_date_inerval = $date_num . " " . $date_unit;
        $sql    = "SELECT 
  a.BW,
  b.AVG_BW,
  DATE(a.Weight_Date) Weight_Date,
  a.Notes 
FROM
  self.Adonis a
  JOIN(SELECT 
 AVG(BW) AVG_BW,
 WEEK(Weight_Date) Weight_Week,
  MONTH(Weight_Date) Weight_Month,
  YEAR(Weight_Date) Weight_Year
FROM
  self.Adonis 
WHERE Weight_Date > DATE_SUB(CURDATE(), INTERVAL $sql_date_inerval) 
  AND User_ID = $user 
 GROUP BY YEAR(Weight_Date),MONTH(Weight_Date),WEEK(Weight_Date)) b
	ON YEAR(a.Weight_Date) = b.Weight_Year
	AND MONTH(a.Weight_Date) = b.Weight_Month
	AND WEEK(a.Weight_Date) = b.Weight_Week
WHERE a.Weight_Date > DATE_SUB(CURDATE(), INTERVAL $sql_date_inerval) 
  AND a.User_ID = ? 
  ORDER BY a.Weight_Date ASC";

        $query = $db->prepare($sql);
        $query->bind_param('i', $user);
  $query->execute();
$query->store_result();
$query->bind_result($BW, $AvgBW,$BW_Date, $Notes);
$BW_a = "[";
$AvgBW_a = "[";
$BW_Date_a    = "[";
$Notes_a      = "[";
$count = 0;
while ($query->fetch()) {
    $BW_a .= "'$BW',";
    $AvgBW_a .= "'$AvgBW',";
    $BW_Date_a  .= "'$BW_Date',";
    $Notes_a .= "'$Notes',";
}
$BW_a = rtrim($BW_a,",");
$AvgBW_a = rtrim($AvgBW_a,",");
$BW_Date_a = rtrim($BW_Date_a,",");
$Notes_a = rtrim($Notes_a,",");
$BW_a .= "]";
$AvgBW_a .= "]";
$BW_Date_a  .= "]";
$Notes_a .= "]";

$return_array = [$BW_Date_a,$BW_a,$AvgBW_a,$Notes_a];

return $return_array;
    }

    function conn(){
        $admin_name = 'server';
$admin_password = 'serverpass';
$admin_db = 'news';
$admin_server ='localhost';
$NaCL	= '@^#4$}j6131';

$db = new mysqli("$admin_server", "$admin_name" ,"$admin_password","$admin_db");

return $db;
}

    public function recordMeal($user,$url)
    {
        echo "<div class=\"col-5\">Meal Tracker<br>
  <link rel=\"stylesheet\" href=\"../core/scripts/datetimepick/jquery.datetimepicker.css\">
  <script src=\"../core/scripts/datetimepick/node_modules/php-date-formatter/js/php-date-formatter.min.js\"></script>
    <script src=\"../core/scripts/datetimepick/node_modules/jquery-mousewheel/jquery.mousewheel.js\"></script>
  <script src=\"../core/scripts/datetimepick/jquery.datetimepicker.js\"></script>

  <script>
  $( function() {
    $( \"#datetimepicker\" ).datetimepicker({step:10});
  } );
  </script>";
        $today  = date('Y-m-d');
        echo "
    <form style=\"width: 60%\" action=\"../core/scripts/php/update.php\" method=\"post\">
      <div class=\"form-group\">
    <select name = \"Meal_Name\">
            <option value = \"\" selected></option>
            <option value = \"0\">Breakfast</option>
            <option value = \"1\">Lunch</option>
            <option value = \"2\">Dinner</option>
            <option value = \"3\">Other AM</option>
            <option value = \"4\">Other PM</option>
            <option value = \"5\">Start Fast</option>
            <option value = \"6\">End Fast</option>
         </select>
  </div>
  <div class=\"form-group\">
    <select name = \"Meal_Rate\">
            <option value = \"\" selected></option>
            <option value = \"1\">Good</option>
            <option value = \"2\">Bad</option>
            <option value = \"3\">Low/0 Carb</option>
         </select>
  </div>
  <div class=\"form-group\">
    <input type=\"text\" id=\"datetimepicker\" class=\"form-control\" name=\"datetime\">
    <input type=\"hidden\" id=\"return_url\" name=\"return_url\" value=\"$url\">
    <input type=\"hidden\" id=\"user_id\" name=\"user\" value=\"$user\">
    <input type=\"hidden\" id=\"action\" name=\"action\" value=\"record_meal\">
  </div>
  <button type=\"submit\" class=\"btn btn-primary\">Submit</button>
</form>
</div>";

    }

}
?>