<?php
/**
 * PLUGIN NAME: Motheo Reports (Enrolment Graphs and/or Charts)
 * DESCRIPTION: Create graphs for overall project enrolments, and bayley score graphs
 * VERSION: 1.0
 * AUTHOR: Ame N. Diphoko
 */

// Call the REDCap Connect file in the main "redcap" directory
require_once "../redcap_connect.php";

// OPTIONAL: Your custom PHP code goes here. You may use any constants/variables listed in redcap_info().

// Limit this plugin only to project_id 17, Motheo.
$projects = [ 17, ];
REDCap::allowProjects($projects);

// OPTIONAL: Display the project header
require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php';

/**
 * CHANGE THIS TO YOUR DATE FIELD NAME
 */
$date_field_name = 'eligibility_date';
$site_field_name = 'eligibility_enrol_site';
$cohort_field_name = 'mat_enrol_cohort';

$temp_data = [
    "months" => [],
    "cumulative" => [],
    "molepolole" => [],
    "gaborone" => [],
    "negative" => [],
    "positive" => []
];

// Enrolment data
$result = REDCap::getData('array', null, [ $date_field_name, $site_field_name, $cohort_field_name ]);

// TODO: get sites array automatically from dictionary => data mapping.
//$fields = array('eligibility_enrol_site',);
//$dd_array = REDCap::getDataDictionary(17, 'array', false, $fields);
//$sites_map = $dd_array['eligibility_enrol_site']['select_choices_or_calculations'];
//$sites_map = preg_split("/[\s,|]+/", $sites_map);
$sites_map = array(20 => "molepolole", 40 => "gaborone");
$cohort_map = array(0 => "negative", 1 => "positive");

foreach ($result as $record_id => $record) {
    /**
     * The assumption is that your field exists on a
     * non-repeating instrument, on the first event in the project.
     *
     * If your project uses multiple events, you'll need to identify
     * the specific event_id that's associated with the field you
     * want to report on, and change the following line.
     *
     * FROM
     * $date_raw = $record[$Proj->firstEventId][$date_field_name];
     *
     * TO
     * $date_raw = $record[1234][$date_field_name];
     * where 1234 is the event_id
     */
//    $date_raw = $record[$Proj->firstEventId][$date_field_name];

    $date_raw = $record[42][$date_field_name];
    if (empty($date_raw)) continue;

    $date = (new \DateTime($date_raw))
        ->modify('first day of this month')
        ->format('Y-m-d');
    if (!array_key_exists($date, $temp_data["months"])) {
        $temp_data["months"][$date] = 0;
    }
    $temp_data["months"][$date]++;
    
    $site_raw = $record[42][$site_field_name];
    if(empty($site_raw)) continue;
    $site = $sites_map[$site_raw];

    if (!array_key_exists($date, $temp_data[$site])) {
        $temp_data[$site][$date] = 0;
    }
    $temp_data[$site][$date]++;

    $cohort_raw = $record[42][$cohort_field_name];

    if(!isset($cohort_raw)) continue;
    $cohort = $cohort_map[$cohort_raw];

    if(!array_key_exists($site, $temp_data[$cohort])) {
        $temp_data[$cohort][$site] = 0;
    }
    $temp_data[$cohort][$site]++;
}

ksort($temp_data["months"]);
krsort($temp_data["molepolole"]);
krsort($temp_data["gaborone"]);

$cumulative_count = 0;
foreach ($temp_data["months"] as $date => $count) {
    $cumulative_count += $count;
    $temp_data["cumulative"][$date] = $cumulative_count;
}

$site_months = array_unique(array_merge(array_keys($temp_data["molepolole"]), array_keys($temp_data["gaborone"])));
arsort($site_months);

$enrol_chart_id = "chart_" . preg_replace('![^\w]+!', '_', uniqid(rand(), true));
$sites_chart_id = "chart_" . preg_replace('![^\w]+!', '_', uniqid(rand(), true));
$sites_pie_id = "pie_" . preg_replace('![^\w]+!', '_', uniqid(rand(), true));
$cohort_bar_id = "bar_" . preg_replace('![^\w]+!', '_', uniqid(rand(), true));

?>
    <div class="container-fluid">
        <!-- Overall Enrolments Charts -->
        <div class="row">
            <div class="col-12 col-md-12">
                <h4>Enrolment over time </h4>
                <h8 style="text-transform:uppercase"><b>Monthly enrolment</b></h8>
            </div>
            <div class="col-12 col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <p>Overall enrolment(s) over time using the <kbd><?=$date_field_name?></kbd> field to plot the </br> chart.</p>
                    </div>
                    <div class="panel-body">
                        <div id="<?=$enrol_chart_id?>" class="chart"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <p>Enrolment(s) by site using the <kbd><?=$date_field_name?></kbd> and <kbd><?=$site_field_name?></kbd> field.</p>
                    </div>
                    <div class="panel-body col-md-12">
                        <div id="<?=$sites_chart_id?>" class="chart"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12 col-md-12">
                <h8 style="text-transform:uppercase"><b>Overall enrolment</b></h8>
            </div>
            <div class="col-12 col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <p>Enrolment(s) by site </p>
                    </div>
                    <div class="panel-body col-md-12">
                        <div id="<?=$sites_pie_id?>" class="chart"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <p style="">Enrolment by cohort (per site)</p>
                    </div>
                    <div class="panel-body col-md-12">
                        <div id="<?=$cohort_bar_id?>" class="chart"></div>
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
            var chartId = "<?=$enrol_chart_id?>";
            var chartConfig = {
                bindto: d3.select("#" + chartId),
                data: {
                    x: 'x',
                    columns: [
                        <?=json_encode(array_merge([ 'x' ], array_keys($temp_data["cumulative"])))?>,
                        <?=json_encode(array_merge([ 'Number Recruited' ], array_values($temp_data["months"])))?>,
                        <?=json_encode(array_merge([ 'Cumulative' ], array_values($temp_data["cumulative"])))?>,
                    ],
                    types: {
                        "Number Recruited": "bar",
                        "Cumulative": "area-spline"
                    }
                },
                bar: {
                    width: 20
                },
                axis: {
                    x: {
                        type: 'timeseries',
                        tick: {
                            format: '%b %Y'
                        }
                    }
                }
            }
            c3.generate(chartConfig);
        });
    </script>
    <script type="text/javascript">
        $(function() {
            var site_chartId = "<?=$sites_chart_id?>";
            var chartConfig = {
                bindto: d3.select("#" + site_chartId),
                data: {
                    x: 'x',
                    columns: [
                        <?=json_encode(array_merge([ 'x' ], array_values($site_months)))?>,
                        <?=json_encode(array_merge([ 'Molepolole' ], array_values($temp_data["molepolole"])))?>,
                        <?=json_encode(array_merge([ 'Gaborone' ], array_values($temp_data["gaborone"])))?>,
                    ],
                },
                axis: {
                    x: {
                        type: 'timeseries',
                        tick: {
                            format: '%b %Y'
                        }
                    }
                },
                zoom: {
                    enabled: true
                }
            }
            c3.generate(chartConfig);
        });
    </script>
    <script type="text/javascript">
        $(function() {
          var site_pieId = "<?=$sites_pie_id?>";
          var pieConfig = {
            bindto: d3.select("#" + site_pieId),
            data: {
                  columns: [
                    <?=json_encode(array_merge([ 'Molepolole' ], array_values($temp_data["molepolole"])))?>,
                    <?=json_encode(array_merge([ 'Gaborone' ], array_values($temp_data["gaborone"])))?>,
                  ],
                  type : 'pie',
                  onclick: function (d, i) { console.log("onclick", d, i); },
                  onmouseover: function (d, i) { console.log("onmouseover", d, i); },
                  onmouseout: function (d, i) { console.log("onmouseout", d, i); }
            },
            legend: {
                show: true,
                position: 'inset'
            },
            tooltip: {
                format: {
                  value: function (value, ratio, id, index) { return value +" ("+ round(ratio*100, 1) + "%)"; }
                }
            },
//            color: {
//                pattern: ['#37A2EB', '#FF6383', ]
//            }
          }
          c3.generate(pieConfig);
        });
    </script>
    <script type="text/javascript">
        $(function() {
          var cohort_barId = "<?=$cohort_bar_id?>";
          var barConfig = {
          bindto: d3.select("#" + cohort_barId),
          data: {
                 columns: [
                    <?=json_encode(array_merge([ 'Molepolole' ], array($temp_data["negative"]["molepolole"], $temp_data["positive"]["molepolole"])))?>,
                    <?=json_encode(array_merge([ 'Gaborone' ], array($temp_data["negative"]["gaborone"], $temp_data["positive"]["gaborone"])))?>,
                 ],
                 type: 'bar'
          },
          bar: {
             width: {
                 ratio: 0.5
             }
          },
          axis: {
             x: {
                 type: 'category',
                 categories: <?=json_encode(array_map('ucfirst', array_values($cohort_map)))?>
             }
          }
        }
          c3.generate(barConfig);
        });
    </script>
<?php
// OPTIONAL: Display the project footer
require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';
