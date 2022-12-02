<?php
/**
 * PLUGIN NAME: Motheo Reports (Bayley Graphs and/or Charts)
 * DESCRIPTION: Create graphs for the bayley summary score values
 * VERSION:     1.0
 * AUTHOR:      Ame N. Diphoko
 */

// Call the REDCap Connect file in the main "redcap" directory
require_once "../redcap_connect.php";

// OPTIONAL: Your custom PHP code goes here. You may use any constants/variables listed in redcap_info().

// Limit this plugin only to project_id 17, Motheo.
$projects = [ 17, ];
REDCap::allowProjects($projects);

// OPTIONAL: Display the project header
require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php';

$subid_fieldname = 'eligibility_subid';
$child_age_fieldname = 'child_age';
$child2_age_fieldname = 'child_age_2';
$score_fieldname = 'cog_summary';
$visit_arm = 'enrolment_visit_arm_1';

$data = array();

// Cognitive scales data
$cog_data = REDCap::getData('json', null, array($subid_fieldname, $child_age_fieldname, $child2_age_fieldname, $score_fieldname), $visit_arm);

foreach(json_decode($cog_data, true) as $element) {
    $subid_raw = $element[$subid_fieldname];
    $data[$subid_raw][$subid_fieldname] = $subid_raw;

    $child_age_raw = $element[$child_age_fieldname];

    if ($child_age_raw) {
        $data[$subid_raw]['child_age_1'] = $child_age_raw;
    }

    $child2_age_raw = $element[$child2_age_fieldname];
    if ($child2_age_raw) {
        $data[$subid_raw]['child_age_2'] = $child2_age_raw;
    }

    $score_raw = $element[$score_fieldname];
    if ($score_raw) {
        $data[$subid_raw]['score'][] = $score_raw;
    }
}

$graph_data = [
    "child_age" => [],
    "score" => []
];

foreach($data as $row) {
    $counter = 0;
    foreach($row['score'] as $score_value) {
        $counter++;
        array_push($graph_data['child_age'], $row['child_age_'.$counter]);
        array_push($graph_data['score'], $score_value);
    }
}

$cog_chart_id = "chart_" . preg_replace('![^\w]+!', '_', uniqid(rand(), true));
?>

<div class="container-fluid">
    <!-- Overall Bayley Charts -->
    <div class="row">
        <div class="col-12 col-md-12">
            <h4>BSIDiii summary score values</h4>
        </div>
        <div class="col-12 col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h8 style="text-transform:uppercase"><b>Cognitive Scale</b></h8>
                </div>
                <div class="panel-body">
                    <div id="<?=$cog_chart_id?>" class="chart"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet prefetch" href="https://cdnjs.cloudflare.com/ajax/libs/c3/0.6.9/c3.min.css">
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/d3/5.7.0/d3.min.js" charset="utf-8"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/c3/0.6.9/c3.min.js"></script>
<script type="text/javascript">
    $(function() {
        var chartId = "<?=$cog_chart_id?>";
        var chartConfig = {
            bindto: d3.select("#" + chartId),
            data: {
                xs: {score: 'child_age'},
                columns: [
                    <?=json_encode(array_merge([ 'score' ], array_keys($graph_data["score"])))?>,
                    <?=json_encode(array_merge([ 'child_age' ], array_values($graph_data["child_age"])))?>,
                ],
                type: 'scatter'
            },
            axis: {
                x: {
                    label: 'Child Age (months)',
                    tick: {
                        fit: false
                    }
                },
                y: {
                    label: 'Cognitive summary score'
                }
            }
        }
        c3.generate(chartConfig);
    });
</script>

<?php
// OPTIONAL: Display the project footer
require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';

