<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminBaseController;
use App\Repository\Eloquent\NotifyCationRepository;
use App\Repository\Eloquent\DeviceTokenRepository;
use App\Repository\Eloquent\FCMTokenRepository;
use Validator;

class NotifyController extends AdminBaseController
{
    protected $notifyCationRepository;
    protected $deviceTokenRepository;
    protected $FCMTokenRepository;

    public function __construct(
        NotifyCationRepository $notifyCationRepository,
        DeviceTokenRepository $deviceTokenRepository,
        FCMTokenRepository $FCMTokenRepository
        )
    {
        parent::__construct();
        $this->notifyCationRepository = $notifyCationRepository;
        $this->deviceTokenRepository = $deviceTokenRepository;
        $this->FCMTokenRepository = $FCMTokenRepository;
    }

    public function index(Request $request){
        $currentPage = isset($request->page) ? $request->page : 1;
        $total = $this->notifyCationRepository->countListNotify();
        // Record counts in a page.
        $numberPerPage = config('constants.NUMBER_PERPAGE');
        $totalPage = ceil($total / $numberPerPage);
        if (!$currentPage || !is_numeric($currentPage) || $currentPage < 1) {
            $currentPage = 1;
        }
        if ($currentPage > $totalPage) {
            $currentPage = $totalPage;
        }
        
        $offset = ($currentPage - 1) * $numberPerPage;
        $orderBy = [];
        $order = 'created_at';
        $sort = 'desc';
        $orderBy = [$order, $sort];
        $page = [
            "total" => $total,
            "totalPage" => $totalPage,
            "currentPage" => $currentPage
        ];
        $results = $this->notifyCationRepository->getListNotify($numberPerPage, $offset, $orderBy);
        return view('admin.notify.index',compact('results','page', 'numberPerPage'));
    }

    public function store(Request $request){
        $rules = [
            'notify_title' => 'required',
            'notify_message' => 'required',
        ];
        $messages = [
            'required' => 'タイトルとメッセージの両方を入力してください',
        ];
        Validator::make($request->all(), $rules, $messages)->validate();
        

        $data_insert = [
            'type' => $request->notify_type,
            'title' => $request->notify_title,
            'message' => $request->notify_message,
        ];
        try{
            $this->notifyCationRepository->create($data_insert);
            if (strtoupper(substr(php_uname(), 0, 7)) === 'WINDOWS') {
                $command = 'cd "' . base_path() . '" && start /B php artisan pushNotify:send '. $data_insert['type'];
            } else {
                $command = 'cd "' . base_path() . '" && php artisan pushNotify:send '. $data_insert['type'] .' > /dev/null 2>&1 &';
            }
            exec($command);
            return redirect()->route('notify.index')->withSuccess('お知らせのプッシュ通知を送信しました');
        }
        catch(\Exception $e){
            return redirect()->route('notify.index')->withErrors('errors');
        }
    }
}
