<?php

return [

    /*
    |--------------------------------------------------------------------------
    | HRMS Settings
    |--------------------------------------------------------------------------
    |
    | Application-specific configuration for the HRMS platform.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Annual Leave Allowance
    |--------------------------------------------------------------------------
    |
    | The default number of paid leave days granted to each employee per
    | calendar year. This is used to compute the remaining leave balance
    | shown on the employee dashboard and leave pages. It can be overridden
    | via the HRMS_ANNUAL_LEAVE_ALLOWANCE environment variable.
    |
    */

    'annual_leave_allowance' => (int) env('HRMS_ANNUAL_LEAVE_ALLOWANCE', 20),

];
