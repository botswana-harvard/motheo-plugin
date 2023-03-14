<?php

namespace moso;

use REDCap;

class moso
{
    public $project_id;
    public $currentWeek;
    public $prevWeek;

    public function __construct($project_id)
    {
        $this->project_id = $project_id;
        $this->currentWeek = date('W');
        $this->prevWeek = date('W', strtotime('-1 week'));
        REDCap::allowProjects([$this->project_id, 23, 26]);
    }

    public function get_data()
    {
        $data = REDCap::getData('json', null, array('bid', 'ac_child_age', 'ac_cklst_date', 'gender',
            'what_is_the_study_site'), ['enrollment_arm_1']);
        return json_decode($data, true);
    }

    public function get_hiv_elisa_data()
    {
        $data = REDCap::getData('json', null, array('bid', 'today_date_elisa', 'hiv_elisa_result', 'gender',
            'what_is_the_study_site'));
        return json_decode($data, true);
    }

}