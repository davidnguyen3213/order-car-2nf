<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Validator;

class LoginAdminController extends Controller
{

    public function getLogin()
    {
        return view('layout.login');
    }

    public function postLogin(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ];
        $messages = [
            'email.required' => __('Email là trường bắt buộc'),
            'email.email' => __('Email không đúng định dạng'),
            'password.required' => __('Mật khẩu là trường bắt buộc'),
            'password.min' => __('Mật khẩu phải chứa ít nhất 8 ký tự'),
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $data = [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ];

        if (!Auth::attempt($data)) {
            $errors = new MessageBag(['errorlogin' => __('Email hoặc mật khẩu không đúng')]);
            return redirect()->back()->withInput()->withErrors($errors);
        }

        return redirect()->intended('/');
    }

    public function getLogout()
    {
        Auth::logout();

        return redirect()->intended('/login');
    }
}