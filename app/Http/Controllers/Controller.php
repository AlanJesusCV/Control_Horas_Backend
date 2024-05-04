<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\SodiumUtil;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use function App\Helpers\formatErrorResponse;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function encriptar($encriptar){
        $encriptar = SodiumUtil::encryptData($encriptar);
        return response()->json(formatErrorResponse(false, $encriptar, []));
    }

    public function desencriptar($encriptar){
        $encriptar = SodiumUtil::decryptData($encriptar);
        return response()->json(formatErrorResponse(false, $encriptar, []));
    }
}
