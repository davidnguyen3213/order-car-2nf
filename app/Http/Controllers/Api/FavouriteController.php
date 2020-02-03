<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Requests\FavouriteRequest;
use App\Repository\Eloquent\FavouriteRepository;


class FavouriteController extends BaseController
{

    protected $favouriteRepository;

    public function __construct(FavouriteRepository $favouriteRepository)
    {
        $this->favouriteRepository = $favouriteRepository;
    }

    /**
     * Favourite api
     *
     * @param FavouriteRequest $request
     * @return \Illuminate\Http\Response
     */
    public function favourite(FavouriteRequest $request)
    {
        try {
            $input = $request->all();
            $favourite = $this->favouriteRepository->firstWhere([
                'user_id' => $input['user_id'],
                'unregistered_company_id' => $input['unregistered_company_id']
            ]);

            $statusFavourite = true;
            if (empty($favourite) && $input['favourite'] == \Config::get('constants.FAVOURITE')) {
                // Create favourite
                $this->favouriteRepository->create([
                    'user_id' => $input['user_id'],
                    'unregistered_company_id' => $input['unregistered_company_id']
                ]);
            } else if (!empty($favourite) && $input['favourite'] != \Config::get('constants.FAVOURITE')) {
                // Remove favourite
                $this->favouriteRepository->delete($favourite['id']);
            } else {
                // Not favourite
                $statusFavourite = false;
            }

            return $statusFavourite ?
                $this->sendResponse([], __('Successfully.'))
                : $this->sendResponse([], __('気に入りの値は不正です。'), true);

        } catch (\Exception $ex) {
            return $this->sendError(__($ex->getMessage()));
        }
    }
}