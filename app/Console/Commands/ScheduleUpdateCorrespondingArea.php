<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repository\Eloquent\CompanyRepository;
use App\Repository\Eloquent\CorrespondingAreaRepository;
use App\Repository\Eloquent\UnregisteredCompanyRepository;

class ScheduleUpdateCorrespondingArea extends Command
{
    protected $companyRepository;
    protected $correspondingAreaRepository;
    protected $unregisteredCompanyRepository;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:update-corresponding-area  {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command check update corresponding area';
    
    public function __construct(
        CompanyRepository $companyRepository,
        CorrespondingAreaRepository $correspondingAreaRepository,
        UnregisteredCompanyRepository $unregisteredCompanyRepository

    )
    {
        parent::__construct();
        $this->companyRepository = $companyRepository;
        $this->correspondingAreaRepository = $correspondingAreaRepository;
        $this->unregisteredCompanyRepository = $unregisteredCompanyRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $type = $this->argument('type');
        //For company
        if ($type == \Config::get('constants.TYPE_AREA.COMPANY')) {
            $companies = $this->companyRepository->findWhereAll([
                ['corresponding_area', '!=', ''],
                ['corresponding_area', '!=', null]
            ]);
            self::_splitArea($type, $companies);
        }

        //For unregistered company
        if ($type == \Config::get('constants.TYPE_AREA.UNREGISTERED_COMPANY')) {
            $unregisteredCompanyRepository = $this->unregisteredCompanyRepository->findWhereAll([
                ['corresponding_area', '!=', ''],
                ['corresponding_area', '!=', null]
            ]);
            self::_splitArea($type, $unregisteredCompanyRepository);
        }

    }

    /**
     * @param $type
     * @param array $companies
     */
    private function _splitArea($type, $companies = []) {
        if (empty($companies)) {
            return;
        }

        //delete current record
        $this->correspondingAreaRepository->deleteWhere([
            ['type', '=', $type]
        ]);

        $dataArea = [];
        foreach ($companies as $key => $company) {
            $areaArray = explode(\Config::get('constants.DELIMITER'), $company['corresponding_area']);
            if (!empty($areaArray)) {
                foreach ($areaArray as $key2 => $area) {
                    if (strlen(trim($area)) == 0) {
                        continue;
                    }
                    if (strlen(trim(str_replace(' ', '', $area))) == 0) {
                        continue;
                    }
                    $dataArea[$key . '-' . $key2]['company_id'] = $company['id'];
                    $dataArea[$key . '-' . $key2]['corresponding_area'] = $area;
                    $dataArea[$key . '-' . $key2]['type'] = $type;
                    $dataArea[$key . '-' . $key2]['created_at'] = date('y-m-d H:i:s');
                    $dataArea[$key . '-' . $key2]['updated_at'] = date('y-m-d H:i:s');
                }
            }
        }

        //insert to corresponding area
        if (!empty($dataArea)) {
            $this->correspondingAreaRepository->insertMultipleRows($dataArea);
        }
    }
}
