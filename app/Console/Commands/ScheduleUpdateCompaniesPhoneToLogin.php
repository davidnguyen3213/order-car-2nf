<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repository\Eloquent\CompanyRepository;


class ScheduleUpdateCompaniesPhoneToLogin extends Command
{
    protected $companyRepository;


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:update-companies-phone';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Command check update companies's phone to login";

    public function __construct(
        CompanyRepository $companyRepository
    )
    {
        parent::__construct();
        $this->companyRepository = $companyRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //For company
        $companies = $this->companyRepository->all();
        if (!empty($companies)) {
            foreach ($companies as $company) {
                $this->companyRepository->update([
                    'phone_to_login' => str_replace('-', '', trim($company['phone']))
                ], $company['id']);
            }
        }

    }
}
