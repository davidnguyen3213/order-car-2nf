<?php

namespace App\Console\Commands;

use App\Helpers\PushNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:send {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command send notifications';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $file = $this->argument('file');
        if (!file_exists($file)) {
            Log::error('File "'. $file.'" not found!');
            return;
        }
        try {
            $fileContent = file_get_contents($file);
            $companies = json_decode($fileContent, true);
            foreach ($companies as $company) {
                PushNotification::sendNotification($company[0], $company[1]['android'], $company[1]['ios']);
            }
            unlink($file);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
        }
    }
}
