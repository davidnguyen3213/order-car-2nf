<?php

namespace App\Repository\Contracts;

use App\Repository;

interface RequestUserInterface extends RepositoryInterface
{
    public function requestUser($requestID);

    public function listRequestUserSearchAddress($address, $companyID);

    public function countUnreadByAddress($address, $companyID);

    public function listFrequencyRequestUser($userID, $columnGroupBy, $columnCheckDeleted);

    public function listUnexpiredRequestOfUser($userId);

    public function searchWithRequestUser(array $searchCondition = [], int $limit = 0, int $offset = 0, array $orderBy = []);

    public function countSearchWithRequestUser(array $searchCondition = []);

    public function collectionRequestUserExpired();
}
