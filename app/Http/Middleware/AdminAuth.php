<?php

namespace App\Http\Middleware;

use App\Repository\Eloquent\UserRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;


class AdminAuth
{

    protected $userRepository;

    public function __construct(
        UserRepository $userRepository
    )
    {
        $this->userRepository = $userRepository;
    }

    public function handle($request, $next)
    {
        $user_id = $request->session()->get("admin_user_id");

        if(!$user_id)
        {
            return redirect(route("login"));
        }

        $condition = [
            ['id', "=", $user_id],
            ['type', "=", config('constants.TYPE_USER.WEB_ADMIN')]
        ];
        $user = $this->userRepository->firstWhere($condition);

        if (!$user) {
            return redirect(route("logout"));
        }

        $request->loginedUser = $user;
        return $next($request);
    }
}