<?php
/*
 * read_counts.php
 */
 
include('common_functions.php');
$config_parameters = parse_ini_file("../../config_clearance.ini", true);

//configuration of database
$db_host = $config_parameters['database_setup']['host'];
$db_user = $config_parameters['database_setup']['user'];
$db_password = $config_parameters['database_setup']['password'];
$db = $config_parameters['database_setup']['database'];

$count_time_short = $config_parameters['counting_parameters']['counting_time_short'];
$count_time_long = $config_parameters['counting_parameters']['counting_time_long'];

$connection_error = False;
$con = mysqli_connect($db_host,$db_user,$db_password,$db);
// Check connection
if (mysqli_connect_errno()){
    $connection_error = True;
    $error_message = '<div class="body_text">Failed to connect to MySQL: ' . mysqli_connect_error() . ' </div>';
}

echo(html_header());
 
if($connection_error){
    echo $error_message;
    echo html_footer();
} else {

	echo('<div class="datacontainer">');

    $patient_cpr = $_GET['patient_cpr'];
    $patient_exam_date = $_GET['patient_exam_date'];
    $std = $_GET['std'];
    $accession_number = $_GET['accession_number'];
    $measurement_type = $_GET['measurement_type'];
    
    
    //find measured data from the patient examination. accession_number is unique so no reason for "limit 1"
    $result_examination = mysqli_query($con, "select * FROM patient_values WHERE accession_number='$accession_number'");
    $row_examination = mysqli_fetch_array($result_examination);
    if($measurement_type == 'V3_24' || $measurement_type == 'B2_24'){
	    $sample_0_count_time = $row_examination['sample_0_count_time'] == '' ? $count_time_long : $row_examination['sample_0_count_time'];
	    $sample_1_count_time = $row_examination['sample_1_count_time'] == '' ? $count_time_long : $row_examination['sample_1_count_time'];
	    $sample_2_count_time = $row_examination['sample_2_count_time'] == '' ? $count_time_long : $row_examination['sample_2_count_time'];
	    $sample_3_count_time = $row_examination['sample_3_count_time'] == '' ? $count_time_long : $row_examination['sample_3_count_time'];
	    $sample_4_count_time = $row_examination['sample_4_count_time'] == '' ? $count_time_long : $row_examination['sample_4_count_time'];
	    $background_1_count_time = $row_examination['background_1_count_time'] == '' ? $count_time_long : $row_examination['background_1_count_time'];
	    $background_2_count_time = $row_examination['background_2_count_time'] == '' ? $count_time_long : $row_examination['background_2_count_time'];
	    $standard_1_count_time = $row_examination['standard_1_count_time'] == '' ? $count_time_long : $row_examination['standard_1_count_time'];
	    $standard_2_count_time = $row_examination['standard_2_count_time'] == '' ? $count_time_long : $row_examination['standard_2_count_time'];
	} elseif($measurement_type == 'V1' || $measurement_type == 'B1'){
		$sample_0_count_time = $row_examination['sample_0_count_time'] == '' ? $count_time_short : $row_examination['sample_0_count_time'];
	    $sample_1_count_time = $row_examination['sample_1_count_time'] == '' ? $count_time_short : $row_examination['sample_1_count_time'];
	    $sample_2_count_time = $row_examination['sample_2_count_time'] == '' ? $count_time_short : $row_examination['sample_2_count_time'];
	    $background_1_count_time = $row_examination['background_1_count_time'] == '' ? $count_time_short : $row_examination['background_1_count_time'];
	    $background_2_count_time = $row_examination['background_2_count_time'] == '' ? $count_time_short : $row_examination['background_2_count_time'];
	    $standard_1_count_time = $row_examination['standard_1_count_time'] == '' ? $count_time_short : $row_examination['standard_1_count_time'];
	    $standard_2_count_time = $row_examination['standard_2_count_time'] == '' ? $count_time_short : $row_examination['standard_2_count_time'];
	} elseif($measurement_type == 'B3' || $measurement_type == 'V3'){
		$sample_0_count_time = $row_examination['sample_0_count_time'] == '' ? $count_time_short : $row_examination['sample_0_count_time'];
	    $sample_1_count_time = $row_examination['sample_1_count_time'] == '' ? $count_time_short : $row_examination['sample_1_count_time'];
	    $sample_2_count_time = $row_examination['sample_2_count_time'] == '' ? $count_time_short : $row_examination['sample_2_count_time'];
	    $sample_3_count_time = $row_examination['sample_3_count_time'] == '' ? $count_time_short : $row_examination['sample_3_count_time'];
	    $sample_4_count_time = $row_examination['sample_4_count_time'] == '' ? $count_time_short : $row_examination['sample_4_count_time'];
	    $sample_5_count_time = $row_examination['sample_5_count_time'] == '' ? $count_time_short : $row_examination['sample_5_count_time'];
	    $sample_6_count_time = $row_examination['sample_6_count_time'] == '' ? $count_time_short : $row_examination['sample_6_count_time'];
	    $background_1_count_time = $row_examination['background_1_count_time'] == '' ? $count_time_short : $row_examination['background_1_count_time'];
	    $background_2_count_time = $row_examination['background_2_count_time'] == '' ? $count_time_short : $row_examination['background_2_count_time'];
	    $standard_1_count_time = $row_examination['standard_1_count_time'] == '' ? $count_time_short : $row_examination['standard_1_count_time'];
	    $standard_2_count_time = $row_examination['standard_2_count_time'] == '' ? $count_time_short : $row_examination['standard_2_count_time'];
	}
    $dir = 'ClearanceResults';
    $files = array();

    //loop over the directory and find .txt files
    if ($handle = opendir($dir)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != ".." && strtolower(substr($entry, strrpos($entry, '.') + 1)) == 'txt') {
                array_push($files, $entry);
            }
        }
        closedir($handle);
    }
    
    $number_of_patients_inserted = array();
    $number_of_patients_missing_database = array();
    
    $old_files = array();
	for($k=0; $k<sizeof($files); $k++){
		$file_date = date("Y-m-d", filemtime($dir . '/' . $files[$k]));	
		if((strtotime($patient_exam_date)-filemtime($dir . '/' . $files[$k])) > 24*60*60*21 xor (strtotime($patient_exam_date)-filemtime($dir . '/' . $files[$k])) <= -24*60*60){
			array_push($old_files, $files[$k]);
		}
	}
	
	$relevant_files = array_filter(array_values(array_diff($files, $old_files)));
	
	//print_r($relevant_files);
    
    for($k=0; $k<sizeof($relevant_files); $k++){
        $file_lines = file($dir . '/' . $relevant_files[$k]);

        $line_element = array();
        for($i=0; $i<sizeof($file_lines); $i++){
            $line_element[$i] = explode("\t", $file_lines[$i]);
            $line_element[$i] = array_filter($line_element[$i]);
            $line_element[$i] = array_slice($line_element[$i], 0);
        }
    
        //find well number and the counts associated
        $counts_well = array();
        $counts_line = array();
        for($i=0; $i<sizeof($line_element); $i++){
            if(preg_grep("/^A.*/", $line_element[$i])) {
                $counts_well[$line_element[$i][key(preg_grep("/^A.*/", $line_element[$i]))]] = $line_element[$i][key(preg_grep("/^A.*/", $line_element[$i])) + 2];
                $counts_line[$i] = $line_element[$i][key(preg_grep("/^A.*/", $line_element[$i])) + 2];
            } elseif(preg_grep("/^B.*/", $line_element[$i])) {
                $counts_well[$line_element[$i][key(preg_grep("/^B.*/", $line_element[$i]))]] = $line_element[$i][key(preg_grep("/^B.*/", $line_element[$i])) + 2];
                $counts_line[$i] = $line_element[$i][key(preg_grep("/^B.*/", $line_element[$i])) + 2];
            } elseif(preg_grep("/^C.*/", $line_element[$i])) {
                $counts_well[$line_element[$i][key(preg_grep("/^C.*/", $line_element[$i]))]] = $line_element[$i][key(preg_grep("/^C.*/", $line_element[$i])) + 2];
                $counts_line[$i] = $line_element[$i][key(preg_grep("/^C.*/", $line_element[$i])) + 2];
            } elseif(preg_grep("/^D.*/", $line_element[$i])) {
                $counts_well[$line_element[$i][key(preg_grep("/^D.*/", $line_element[$i]))]] = $line_element[$i][key(preg_grep("/^D.*/", $line_element[$i])) + 2];
                $counts_line[$i] = $line_element[$i][key(preg_grep("/^D.*/", $line_element[$i])) + 2];
            } else {
                //not a well match
            }
        }
	
        //find the cpr number in the .txt file    
        $cpr_array = array();
        $cpr_lines = array();
        $cpr_value_lines = array();
        $test_array = array();
        $std_array = array();
        $std_array_number = array();
        $bg_array = array();
        for($i=0; $i<sizeof($line_element); $i++){
            if(preg_match("/^\\d{6}-\\d{4}/", $line_element[$i][0])){
                //array[CPR][line number -1 = array index]
                $cpr_array[substr($line_element[$i][0],0,11)] = $i;
        
                //array with line numbers where we have a CPR
                array_push($cpr_lines, $i);
                //array with CPRs (convenience array)
                array_push($cpr_value_lines, $line_element[$i][0]);
                array_push($test_array, array($line_element[$i][0], $i));
                
			} elseif(substr($line_element[$i][0],0,3) == 'std' || substr($line_element[$i][0],0,3) == 'Std'){
				//array with counts from standards
				$std_number = preg_replace('/\s+/', '', substr($line_element[$i][0],4,5));
				$std_array_number[$std_number][] = $line_element[$i][3];
				array_push($std_array, $line_element[$i][3]);
			} elseif($line_element[$i][0] == 'bgg' || $line_element[$i][0] == 'bagg' || $line_element[$i][0] == 'bg' || $line_element[$i][0] == 'Bg'){
				array_push($bg_array, $line_element[$i][3]);
            } else {
                //there is no preg_match. Line is not a cpr number, bg or standard
            }
        }
	    
		$counts_well = array_slice($counts_well, 1);
		$counts_line = array_slice($counts_line, 1);
		//add the end line number of the file
		array_push($cpr_lines, sizeof($line_element));
	
		foreach($cpr_value_lines as $key => $value){
			$cpr_value_lines[$key] = str_replace('-', '', $value);
		}
	
		$patient_data = array();
		
		$bg_array = array();
		
        for($i=0; $i<=sizeof($cpr_lines); $i++){
            for($j=$cpr_lines[$i]; $j<$cpr_lines[$i+1]; $j++){
				if($counts_line[$j-1] != '(Flagged)'){
	                if($j == $cpr_lines[$i]){
	                    $patient_data[$cpr_value_lines[$i]]['sample_0_counts'] = round($counts_line[$j-1]*$sample_0_count_time/60.0,0);
	                } elseif($line_element[$j][0] == '180-1' || $line_element[$j][0] == '120-1'){
	                    $patient_data[$cpr_value_lines[$i]]['sample_1_counts'] = round($counts_line[$j-1]*$sample_1_count_time/60.0,0);
	                } elseif($line_element[$j][0] == '180-2' || $line_element[$j][0] == '120-2'){
	                    $patient_data[$cpr_value_lines[$i]]['sample_2_counts'] = round($counts_line[$j-1]*$sample_2_count_time/60.0,0);
	                } elseif($line_element[$j][0] == '24-1' || $line_element[$j][0] == '24t-1' || $line_element[$j][0] == '240-1'){
	                    $patient_data[$cpr_value_lines[$i]]['sample_3_counts'] = round($counts_line[$j-1]*$sample_3_count_time/60.0,0);
	                } elseif($line_element[$j][0] == '24-2' || $line_element[$j][0] == '24t-2' || $line_element[$j][0] == '240-2'){
	                    $patient_data[$cpr_value_lines[$i]]['sample_4_counts'] = round($counts_line[$j-1]*$sample_4_count_time/60.0,0);
	                } elseif($line_element[$j][0] == '300-1'){
	                    $patient_data[$cpr_value_lines[$i]]['sample_5_counts'] = round($counts_line[$j-1]*$sample_5_count_time/60.0,0); 
	                } elseif($line_element[$j][0] == '300-2'){
	                    $patient_data[$cpr_value_lines[$i]]['sample_6_counts'] = round($counts_line[$j-1]*$sample_6_count_time/60.0,0);
	                } elseif($line_element[$j][0] == 'tom'){
	                    #$patient_data[$cpr_value_lines[$i]]['background_2_counts'] = round($counts_line[$j-1]*$background_2_count_time/60.0,0);
	                    $bg_counts = round($counts_line[$j-1]*$background_1_count_time/60.0,0);
	                    array_push($bg_array,$bg_counts);
	                } elseif($line_element[$j][0] == 'bgg' || $line_element[$j][0] == 'bagg' || $line_element[$j][0] == 'bg'){
						if($counts_line[$j-1] != '(Flagged)'){
							#$patient_data[$cpr_value_lines[$i]]['background_1_counts'] = round($counts_line[$j-1]*$background_1_count_time/60.0,0);
							$bg_counts = round($counts_line[$j-1]*$background_1_count_time/60.0,0);
							array_push($bg_array,$bg_counts);
						}
	                } elseif(strtolower(substr($line_element[$j][0], 0, 3)) == 'std'){
	                    $patient_data[$cpr_value_lines[$i]]['standard_1_counts'] = round($counts_line[$j-1]*$standard_1_count_time/60.0,0);
	                }
	                //Add the standard counts to the array
	                $patient_data[$cpr_value_lines[$i]]['standard_2_counts'] = round($std_array_number[$std][rand(0,sizeof($std_array)-1)]*$standard_1_count_time/60.0,0);
				}
				$patient_data[$cpr_value_lines[$i]]['background_1_counts'] = $bg_array[array_rand($bg_array)];
				$patient_data[$cpr_value_lines[$i]]['background_2_counts'] = $bg_array[array_rand($bg_array)];
            }
        }
			//echo '<pre>';
	        //print_r($patient_data);
	        //echo '</pre>';
	        //FIND A BETTER QUERY!!! THIS WILL NOT ALWAYS WORK!
	        //$query_find_patient = "SELECT accession_number FROM patient_data WHERE patient_cpr='$patient_cpr' and patient_exam_date BETWEEN '$file_date_reformat' - INTERVAL 7 DAY and '$file_date_reformat' LIMIT 1";
	        $query_find_patient = "SELECT accession_number FROM patient_data WHERE patient_cpr='$patient_cpr' and patient_exam_date='$patient_exam_date' LIMIT 1";
	        //echo $query_find_patient . '<br>';
	        $result = mysqli_query($con, $query_find_patient);
	        if(mysqli_num_rows($result) > 0  && $std !== ''){
	            $row = mysqli_fetch_array($result);
	            $accession_number = $row['accession_number'];
	            
	            //create the sql queries
				reset($patient_data);
				for($i=0; $i<sizeof($patient_data); $i++){
					//echo $files[$k] . ' : ' . key($patient_data) . ' : ' . $patient_cpr . '<br>';
					
					if(key($patient_data) == $patient_cpr){
						$query = 'update patient_values set ';
						if(!array_key_exists('background_1_counts', $patient_data[key($patient_data)])){
							$patient_data[key($patient_data)]['background_1_counts'] = round($bg_array[rand(0,sizeof($bg_array)-1)]*$background_1_count_time/60.0,0);
						}
						if(!array_key_exists('background_2_counts', $patient_data[key($patient_data)])){
							$patient_data[key($patient_data)]['background_2_counts'] = round($bg_array[rand(0,sizeof($bg_array)-1)]*$background_2_count_time/60.0,0);
						}
						if(!array_key_exists('standard_1_counts', $patient_data[key($patient_data)])){
							$patient_data[key($patient_data)]['standard_1_counts'] = round($std_array_number[$std][rand(0,sizeof($std_array)-1)]*$standard_1_count_time/60.0,0);
						}
						foreach($patient_data[key($patient_data)] as $key => $value){
							$query = $query . $key . '="' . $value . '", ';
						}
						
						$query = $query . 'counts_file="' . $files[$k] . '"';
						$query = $query . ' where accession_number="' . $accession_number .'"';
						//echo $query . '<br>';
						$result_counts = mysqli_query($con, $query);
						if($result_counts){
	                        echo '<div style="color:#008000">Tællinger indsat på patient (' . $patient_cpr . ') fra filen <a class="txtlink" href="ClearanceResults/' . $files[$k] . '">' . $files[$k] . '</a></div>';
	                    } else {
	                        echo 'Ups. Pinligt! Noget gik galt.<br>';
	                        echo $query;
	                    }
						 
						array_push($number_of_patients_inserted, True);
					} else {
						//echo '<div style="color:#FF0000">Patient-CPR (' . key($patient_data) .') blev ikke fundet i ' . $files[$k] . '</div>';
						array_push($number_of_patients_inserted, False);
					}
					
					next($patient_data);
					
				}
				array_push($number_of_patients_missing_database, True);
	        } else {
				array_push($number_of_patients_missing_database, False);
	            //echo 'Patienten (' . $patient_cpr .') blev ikke fundet. Er injektionstidspunktet angivet?<br>';
	        }
	    }
	
    if(count(array_filter($number_of_patients_missing_database)) < 1){
		echo '<div>Mismatch mellem undersøgelsesdato og tidssstemplet på .txt-filen og/eller ingen standard angivet. Forsøger du at hente målinger, der er mere end 14 dage gamle?</div>';
		echo '<div>Vil du selv kigge <a href="list_datafiles.php?mode=all">.txt-filerne</a> igennem?</div><br>';
	} elseif(count(array_filter($number_of_patients_inserted)) < 1){
		echo '<div style="color:#FF0000">Patient-CPR (' . $patient_cpr . ') blev ikke fundet</div>';
		echo '<div>Vil du selv kigge <a href="list_datafiles.php?mode=matching">.txt-filerne</a> igennem?</div>';
	}
    echo('<input type="button" class="button" value="Tilbage til arbejdsarket" onclick="window.location.href=\'create_worksheet.php?' . parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY) . '\'">');
    echo('</div>');
    echo(html_footer());
}
    
?>
