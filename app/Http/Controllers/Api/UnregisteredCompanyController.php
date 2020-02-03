<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Requests\UnregisteredCompanyCallRequest;
use App\Repository\Eloquent\UnregisteredCompanyRepository;



class UnregisteredCompanyController extends BaseController
{

    protected $unregisteredCompanyRepository;

    public function __construct(UnregisteredCompanyRepository $unregisteredCompanyRepository)
    {
        $this->unregisteredCompanyRepository = $unregisteredCompanyRepository;
    }

    /**
     * Get list unregistered company call
     *
     * @param UnregisteredCompanyCallRequest $request
     * @return \Illuminate\Http\Response
     */
    public function listCall(UnregisteredCompanyCallRequest $request)
    {
        try {
            $input = $request->only('address', 'user_id');
            $listCallUnregisterCompany = $this->unregisteredCompanyRepository->listUnregisteredCompanyCall($input['address'], $input['user_id']);

            return $this->sendResponse($listCallUnregisterCompany->toArray(), __('Successfully.'));
        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }
}