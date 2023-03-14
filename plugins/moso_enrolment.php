<?php
require_once "../redcap_connect.php";
require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php';
require_once './moso/moso.php';

use moso\moso;

$project_id = 24;
$moso_report = new Moso($project_id);

$data = $moso_report->get_data();
$date_field_name = 'ac_cklst_date';
$site_field_name = 'what_is_the_study_site';
$enrol_chart_id = 'enrol_chart_id';
$enrol_site_chart_id = 'enrol_site_chart_id';

?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-12">
                <h4>Enrolment over time </h4>
                <h8 style="text-transform:uppercase"><b>Monthly enrolment</b></h8>
            </div>
            <div class="col-12 col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <p>Overall enrolment(s) over time using the <kbd><?= $date_field_name ?></kbd> field to plot
                            the </br> chart.</p>
                    </div>
                    <div class="panel-body">
                        <div id="<?= $enrol_chart_id ?>" class="chart"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <p>Overall enrolment(s) over time using the <kbd><?= $site_field_name ?></kbd> field to plot
                            the </br> chart.</p>
                    </div>
                    <div class="panel-body">
                        <div id="<?= $enrol_site_chart_id ?>" class="chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.20/lodash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script type="text/javascript">
        let data = <?= json_encode($data) ?>;
        // get the earliest date from the data
        // Create an object to store the data by month
        // Group data by month and year
        const monthlyData = data.reduce((acc, cur) => {
            const date = new Date(cur.ac_cklst_date);
            const year = date.getFullYear();
            const month = date.getMonth();

            acc[year] = acc[year] || {};
            acc[year][month] = acc[year][month] || { enrollments: 0, male: 0, female: 0 };

            acc[year][month].enrollments++;
            if (cur.gender === '1') {
                acc[year][month].male++;
            } else if (cur.gender === '2') {
                acc[year][month].female++;
            }

            return acc;
        }, {});

        // Create arrays for chart data
        const enrollmentData = [];
        const maleData = [];
        const femaleData = [];
        let earliestDate = new Date();

        for (const year in monthlyData) {
            for (const month in monthlyData[year]) {
                const date = new Date(year, month);
                if (date < earliestDate) {
                    earliestDate = date;
                }

                enrollmentData.push([date.getTime(), monthlyData[year][month].enrollments]);
                maleData.push([date.getTime(), monthlyData[year][month].male]);
                femaleData.push([date.getTime(), monthlyData[year][month].female]);
            }
        }

        Highcharts.chart('enrol_chart_id', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'Enrollment by Month and Gender'
            },
            xAxis: {
                type: 'datetime',
                dateTimeLabelFormats: {
                    month: '%b %Y'
                },
                min: earliestDate.getTime()
            },
            yAxis: {
                title: {
                    text: 'Enrollment Count'
                }
            },
            legend: {
                align: 'right',
                verticalAlign: 'middle',
                layout: 'vertical'
            },
            plotOptions: {
                column: {
                    stacking: 'normal'
                }
            },
            series: [{
                type: 'column',
                name: 'Male',
                data: maleData
            }, {
                type: 'column',
                name: 'Female',
                data: femaleData
            }, {
                type: 'line',
                name: 'Total Enrollments',
                data: enrollmentData
            }]
        });


    </script>
    <script type="text/javascript">
        // get the earliest date from the data
        // Create an object to store the data by month
        // Group data by month and year
        const monthlySiteData = data.reduce((acc, cur) => {
            const date = new Date(cur.ac_cklst_date);
            const year = date.getFullYear();
            const month = date.getMonth();

            acc[year] = acc[year] || {};
            acc[year][month] = acc[year][month] || { gaborone: 0, ftown: 0 };

            if (cur.what_is_the_study_site === '1') {
                acc[year][month].gaborone++;
            } else if (cur.what_is_the_study_site === '2') {
                acc[year][month].ftown++;
            }

            return acc;
        }, {});

        // Create arrays for chart data
        const gaboroneData = [];
        const ftownData = [];
        for (const year in monthlySiteData) {
            for (const month in monthlySiteData[year]) {
                const date = new Date(year, month);
                if (date < earliestDate) {
                    earliestDate = date;
                }
                gaboroneData.push([date.getTime(), monthlySiteData[year][month].gaborone]);
                ftownData.push([date.getTime(), monthlySiteData[year][month].ftown]);
            }
        }

        Highcharts.chart('enrol_site_chart_id', {
            chart: {
                type: 'line'
            },
            title: {
                text: 'Enrollment by Site'
            },
            xAxis: {
                type: 'datetime',
                dateTimeLabelFormats: {
                    month: '%b %Y'
                },
                min: earliestDate.getTime()
            },
            yAxis: {
                title: {
                    text: 'Enrollment Count'
                }
            },
            legend: {
                align: 'right',
                verticalAlign: 'middle',
                layout: 'vertical'
            },
            series: [{
                name: 'Gaborone',
                data: gaboroneData
            }, {
                name: 'Francistown',
                data: ftownData
            }]
        });
    </script>

<?php
require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';
