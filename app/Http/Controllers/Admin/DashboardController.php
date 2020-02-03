<?php
namespace App\Http\Controllers\Admin;

use App\Repository\Eloquent\UserRepository;
use App\Http\Controllers\Admin\AdminBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;
use Illuminate\Support\MessageBag;
use Mockery\Exception;
use Validator;

class DashboardController extends AdminBaseController
{
    protected $userRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        UserRepository $userRepository
    )
    {
        parent::__construct();
        $this->userRepository = $userRepository;
    }

    public function top()
    {
        return view('welcome');
    }
}