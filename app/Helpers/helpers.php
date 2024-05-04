<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

if (!function_exists('formatErrorResponse')) {
    function formatErrorResponse($error, $msg, $response = null)
    {
        return [
            'error' => $error,
            'msg' => $msg,
            'response' => $response,
        ];
    }
}
