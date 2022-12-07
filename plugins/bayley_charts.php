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

function prepare_data($subid_fieldname, $child_age_fieldname, $child2_age_fieldname, $score_fieldname, $visit_arm = 'enrolment_visit_arm_1') {
    $data = array();
    $results = REDCap::getData('json', null, array($subid_fieldname, $child_age_fieldname, $child2_age_fieldname, $score_fieldname), $visit_arm);

    foreach(json_decode($results, true) as $element) {
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
    return $graph_data;
}

$subid_fieldname = 'eligibility_subid';
$child_age_fieldname = 'child_age';
$child2_age_fieldname = 'child_age_2';

$cog_score = 'cog_summary';
$cog_data = prepare_data($subid_fieldname, $child_age_fieldname, $child2_age_fieldname, $cog_score);

$reclang_score = 'reclang_summary';
$reclang_data = prepare_data($subid_fieldname, $child_age_fieldname, $child2_age_fieldname, $reclang_score);

$explang_score = 'explang_summary';
$explang_data = prepare_data($subid_fieldname, $child_age_fieldname, $child2_age_fieldname, $explang_score);

$finemot_score = 'finemot_summary';
$finemot_data = prepare_data($subid_fieldname, $child_age_fieldname, $child2_age_fieldname, $finemot_score);

$gross_score = 'gross_summary';
$gross_data = prepare_data($subid_fieldname, $child_age_fieldname, $child2_age_fieldname, $gross_score);

$cog_chart_id = "cog_" . preg_replace('![^\w]+!', '_', uniqid(rand(), true));
$reclang_chart_id = "reclang_" . preg_replace('![^\w]+!', '_', uniqid(rand(), true));
$explang_chart_id = "explang_" . preg_replace('![^\w]+!', '_', uniqid(rand(), true));
$finemot_chart_id = "finemot_" . preg_replace('![^\w]+!', '_', uniqid(rand(), true));
$gross_chart_id = "gross_" . preg_replace('![^\w]+!', '_', uniqid(rand(), true));
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
        <div class="col-12 col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h8 style="text-transform:uppercase"><b>Receptive Language Scale</b></h8>
                </div>
                <div class="panel-body">
                    <div id="<?=$reclang_chart_id?>" class="chart"></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h8 style="text-transform:uppercase"><b>Expressive Language Scale</b></h8>
                </div>
                <div class="panel-body">
                    <div id="<?=$explang_chart_id?>" class="chart"></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h8 style="text-transform:uppercase"><b>Fine Motor Scale</b></h8>
                </div>
                <div class="panel-body">
                    <div id="<?=$finemot_chart_id?>" class="chart"></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h8 style="text-transform:uppercase"><b>Gross Motor Scale</b></h8>
                </div>
                <div class="panel-body">
                    <div id="<?=$gross_chart_id?>" class="chart"></div>
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
        var cog_chartId = "<?=$cog_chart_id?>";
        var chartConfig = {
            bindto: d3.select("#" + cog_chartId),
            data: {
                xs: {score: 'child_age'},
                columns: [
                    <?=json_encode(array_merge([ 'score' ], array_keys($cog_data["score"])))?>,
                    <?=json_encode(array_merge([ 'child_age' ], array_values($cog_data["child_age"])))?>,
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
<script type="text/javascript">
    $(function() {
        var reclang_chartId = "<?=$reclang_chart_id?>";
        var chartConfig = {
            bindto: d3.select("#" + reclang_chartId),
            data: {
                xs: {score: 'child_age'},
                columns: [
                    <?=json_encode(array_merge([ 'score' ], array_keys($reclang_data["score"])))?>,
                    <?=json_encode(array_merge([ 'child_age' ], array_values($reclang_data["child_age"])))?>,
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
                    label: 'Receptive language summary score'
                }
            }
        }
        c3.generate(chartConfig);
    });
</script>
<script type="text/javascript">
    $(function() {
        var explang_chartId = "<?=$explang_chart_id?>";
        var chartConfig = {
            bindto: d3.select("#" + explang_chartId),
            data: {
                xs: {score: 'child_age'},
                columns: [
                    <?=json_encode(array_merge([ 'score' ], array_keys($explang_data["score"])))?>,
                    <?=json_encode(array_merge([ 'child_age' ], array_values($explang_data["child_age"])))?>,
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
                    label: 'Expressive language summary score'
                }
            }
        }
        c3.generate(chartConfig);
    });
</script>
<script type="text/javascript">
    $(function() {
        var finemot_chartId = "<?=$finemot_chart_id?>";
        var chartConfig = {
            bindto: d3.select("#" + finemot_chartId),
            data: {
                xs: {score: 'child_age'},
                columns: [
                    <?=json_encode(array_merge([ 'score' ], array_keys($finemot_data["score"])))?>,
                    <?=json_encode(array_merge([ 'child_age' ], array_values($finemot_data["child_age"])))?>,
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
                    label: 'Fine motor summary score'
                }
            }
        }
        c3.generate(chartConfig);
    });
</script>
<script type="text/javascript">
    $(function() {
        var gross_chartId = "<?=$gross_chart_id?>";
        var chartConfig = {
            bindto: d3.select("#" + gross_chartId),
            data: {
                xs: {score: 'child_age'},
                columns: [
                    <?=json_encode(array_merge([ 'score' ], array_keys($gross_data["score"])))?>,
                    <?=json_encode(array_merge([ 'child_age' ], array_values($gross_data["child_age"])))?>,
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
                    label: 'Gross motor summary score'
                }
            }
        }
        c3.generate(chartConfig);
    });
</script>


<?php
// OPTIONAL: Display the project footer
require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';

