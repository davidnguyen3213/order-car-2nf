<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller as Controller;


class BaseController extends Controller
{
    /**
     * success response method.
     * @param $result
     * @param $message
     * @param $error
     * @param $isConvertObject
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message, $error = false, $isConvertObject = false)
    {
        if ($result instanceof Illuminate\Database\Eloquent\Collection) {
            $result = $result->toArray();
        }

        if (empty($result) && $isConvertObject == true) {
            $result = (object)$result;
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