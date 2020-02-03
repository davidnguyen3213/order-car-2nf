<?php

namespace App\Http\Controllers\Admin\Auth;
use App\Repository\Eloquent\UserRepository;
use App\Http\Controllers\Admin\AdminBaseController;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Hash;
use Validator;


/**
 * @property UserRepository userRepository
 */
class LoginAdminController extends AdminBaseController
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    protected $userRepository;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/admin/user';

    /**
     * Create a new controller instance.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(
        UserRepository $userRepository
    )
    {
        $this->middleware('guest')->except('logout');
        $this->userRepository = $userRepository;
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $rules = [
            'name' => 'required',
            'password' => 'required'
        ];
        $messages = [
            'name.required' => __('ログインIdまたはパスワードが違います。'),
            'password.required' => __('ログインIdまたはパスワードが違います。')
        ];
        $validator = Validator::make($request->all(), $rules,$messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $name = $request->input('name');
        $password = $request->input('password');

        $condition = [
            ['name', "=", $name],
            ['type', "=", config('constants.TYPE_USER.WEB_ADMIN')]
        ];

        $user = $this->userRepository->firstWhere($condition);

        //validate password
        if (!$user || ($user->name != $name) || !Hash::check($password, $user->password)) {
            $errors = new MessageBag(['error_login' => 'ログインIdまたはパスワードが違います。']);
            return redirect()->back()->withInput()->withErrors($errors);
        }

        //save user's login info to db
        $request->session()->flush();
        $request->session()->put("admin_user_id", $user->id);
        $this->guard()->loginUsingId($user->id);

        return redirect(URL::route('user.index'));
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('admin');
    }

    protected function redirectTo()
    {
        return '/admin/user';
    }
}
