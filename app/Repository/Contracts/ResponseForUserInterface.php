<?php

namespace App\Repository\Contracts;

use App\Repository;

interface ResponseForUserInterface extends RepositoryInterface
{
    public function listHistoryByUser($userID, $pagination);

    public function getListResponseByRequestId($request);

    public function getCompanyRequestHistory($companyId, $pagination);

    public function countUnreadResponseOfUser($userId);

    public function countUnapprovedRequest($companyId);

    public function getListResponseCompany($requestId);
}