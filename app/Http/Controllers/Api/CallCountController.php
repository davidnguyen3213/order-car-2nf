<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Requests\CallCountRequest;
use App\Repository\Eloquent\CallCountRepository;

class CallCountController extends BaseController
{

    protected $callCountRepository;

    public function __construct(CallCountRepository $callCountRepository)
    {
        $this->callCountRepository = $callCountRepository;
    }

    /**
     * Get call count
     *
     * @param CallCountRequest $request
     * @return \Illuminate\Http\Response
     */
    public function callCount(CallCountRequest $request)
    {
        try {
            $input = $request->all();
            $this->callCountRepository->create($input);

            return $this->sendResponse([], __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }

}
