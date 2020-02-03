<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

class AdminBaseController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    /**
     * assign data to template
     * @var
     */
    public $viewData;

    public $loginedUser = null;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('admin.auth');
    }

    public function sendResponse($result, $message, $error = false, $isConvertObject = false)
    {
        if ($result instanceof Illuminate\Database\Eloquent\Collection) {
            $result = $result->toArray();
        }

        if (empty($result) && $isConvertObject == true) {
            $result = (object) $result;
        }

        $response = [
            'status' => 200,
            'error' => ($error ? 1 : 0),
            'result' => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @param $error
     * @param $errorMessages
     * @param $code
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'error' => 1,
            'message' => $error,
            'result' => (!empty($errorMessages) ? $errorMessages : []),
            'status' => 404
        ];

        return response()->json($response, $code);
    }
}