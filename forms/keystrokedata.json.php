<?php
/*
@author Thato Mokoena
@date 14/11/18
@location Johannesburg, SA

Process keystroke data generated while the user interacts with the IDE.
Write the data to user specific .csv file.
*/
print_r($_POST);
echo '<script>console.log('.print_r($_POST).')</script>';
define( 'AJAX_SCRIPT', true );

require_once(dirname( __FILE__ ) . '/../../../config.php');
//Make sure that it is a POST request.
if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
    throw new Exception('Request method must be POST!');
}
 
//Make sure that the content type of the POST request has been set to application/json
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if(strcasecmp($contentType, 'application/json') != 0){
    throw new Exception('Content type must be: application/json');
}
 
//Receive the RAW post data.
$content = trim(file_get_contents("php://input"));
 
//Attempt to decode the incoming RAW post data from JSON.
$decoded = json_decode($content, true);
 
//If json_decode failed, the JSON is invalid.
if(!is_array($decoded)){
    throw new Exception('Received content contained invalid JSON!');
}

//Process the JSON.
$userid = $decoded['user_id'];
$keystrokedata = $decoded['keystrokes'];
$dir_path = '/var/www/html/moodle/mod/vpl/keystrokedata/users/code/';
$dir_user = $dir_path . $userid;
//Check if the user already has a folder and if they don't make them a folder using their user_id
if (!file_exists($dir_user)) {
    if(mkdir($dir_user, 0777, true)){
		write_to_csv($dir_user, $keystrokedata, $userid);
	}
	else{
		die('Failed to create folder.');
	}
}
else{
	write_to_csv($dir_user, $keystrokedata, $userid);
}
	
//Write the data to a csv file and save it in the user's folder
function write_to_csv($path, $keystrokedata, $user_id){
	//convert keystrokes to array of arrays
	$data_array = array();

	for ($i = 0; $i < sizeof($keystrokedata); $i++){
        $data_row = array();
        array_push($data_row, $keystrokedata[$i]['press_time'], $keystrokedata[$i]['release_time'], $keystrokedata[$i]['keycode']);
        array_push($data_array, $data_row);
	}
	//setup the filename of the csv file
	$date = new DateTime();
	$time_stamp = $date->getTimestamp();
	$filename = 'user_' . $user_id . '_' . $time_stamp . '.csv';
	//writing to the csv
	$fp = fopen($path . '/' . $filename, 'w');
	fputcsv($fp, array('press_time', 'release_time', 'key_code'));
	foreach ($data_array as $fields) {
		fputcsv($fp, $fields);
		}
		fclose($fp);
	}
?>
