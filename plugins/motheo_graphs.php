<?php
/**
 * PLUGIN NAME: motheo
 * DESCRIPTION: This displays the graphs for project 370
 * VERSION:     1.0
 * AUTHOR:      Sue Lowry - University of Minnesota
 */

// Call the REDCap Connect file in the main "redcap" directory
require_once "../redcap_connect.php";

include_once APP_PATH_DOCROOT . 'Graphical/functions.php';
include_once APP_PATH_DOCROOT . 'ProjectGeneral/form_renderer_functions.php';
include_once APP_PATH_DOCROOT . 'ProjectGeneral/math_functions.php';

// Set value for delay between the rendering of plots on page (to pace them from top to bottom in order)
$plot_pace = 100; // in milliseconds

// OPTIONAL: Your custom PHP code goes here. You may use any constants/variables listed in redcap_info().

// Example of how to restrict this plugin to a specific REDCap project (in case user's randomly find the plugin's URL)
if ($project_id != 17) {
        exit('This plugin is only accessible to users from project "Motheo", which is project_id 17.');
}

// OPTIONAL: Display the project header
require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php';

// Your HTML page content goes here

//print_r($Proj->forms);
//print "<br/>";

// PRINT PAGE button
print  "<div style='text-align:right;max-width:700px;'>
             <button class='jqbuttonmed' onclick='printpage(this)'><img src='".APP_PATH_IMAGES."printer.png' class='imgfix'> Print page</button>
         </div>";

$data = array();

// Cognitive scales data
$cog_data = REDCap::getData('json', null, array('eligibility_subid', 'child_dob', 'child_dob_2', 'cog_summary'), 'enrolment_visit_arm_1');

//print $cog_data;
foreach(json_decode($cog_data, true) as $element) {
//    print $row['child_dob'];
//    array_push($data, array('eligibility_subid'=> $row['eligibility_subid'], 'child_dob'=> $row['child_dob'], 'child_dob_2'=> $row['child_dob_2'], 'score'=> $row['cog_summary']));
//}
//print_r($data);

//$records = array();
//foreach($data as $element) {
    $data[$element['eligibility_subid']]['eligibility_subid'] = $element['eligibility_subid'];
    if ($element['child_dob']) {
        $data[$element['eligibility_subid']]['child_dob_1'] = $element['child_dob'];
    }
    if ($element['child_dob_2']) {
        $data[$element['eligibility_subid']]['child_dob_2'] = $element['child_dob_2'];
    }
    if ($element['cog_summary']) {
        $data[$element['eligibility_subid']]['score'][] = $element['cog_summary'];
    }
}

$graph_data = array();

foreach($data as $row) {
    $counter = 0;
    foreach($row['score'] as $score_value) {
        $counter++;
        array_push($graph_data, array('child_dob'=>$row['child_dob_'.$counter], 'score'=>$score_value));
    }
}

print_r($graph_data);
//print_r($data['01150001']);

?>

<script>
    window.onload = function () {
        var chart = new CanvasJS.Chart("chartContainer", {
            animationEnabled: true,
            exportEnabled: true,
            theme: "light1", // "light1", "light2", "dark1", "dark2"
            title:{
                text: "PHP Column Chart from Database"
            },
            data: [{
                type: "column", //change type to bar, line, area, pie, etc
                dataPoints: <?php echo json_encode($graph_data, JSON_NUMERIC_CHECK); ?>
            }]
        });
        chart.render();
    }
</script>

<div id="chartContainer" style="height: 370px; width: 100%;"></div>
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>

<?php
// OPTIONAL: Display the project footer
require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';

