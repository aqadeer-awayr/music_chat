<?php

namespace App\Http\Controllers;

use App\ChatMessage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Request;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function returnSuccess($message, $data = null)
    {
        $res = [
            'status' => 200,
            'message' => $message,
        ];
        if ($data) {
            $res['data'] = $data;
        }
        return $res;
    }

    public function returnError($message)
    {
        return [
            'status' => 400,
            'message' => $message
        ];
    }

    public function test(Request $request)
    {

    }
}
