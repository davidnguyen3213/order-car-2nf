<?php

namespace App\Console\Commands;

use App\Services\CheckExpiredRequestUserWorker;
use Illuminate\Console\Command;

class ScheduleCheckExpiredRequestUser extends Command
{

    protected $checkExpiredRequestUserWorker;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:check-expired-request-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command check expired request user';
    
    public function __construct(CheckExpiredRequestUserWorker $checkExpiredRequestUserWorker)
    {
        $this->checkExpiredRequestUserWorker = $checkExpiredRequestUserWorker;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->checkExpiredRequestUserWorker->run();
    }
}
