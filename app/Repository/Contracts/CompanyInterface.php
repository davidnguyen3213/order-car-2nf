<?php

namespace App\Repository\Contracts;

use App\Repository;

interface CompanyInterface extends RepositoryInterface
{
    public function listCompanyFollowAddress($address, $requestID);

    public function countCompanyFollowAddress($address);

    /**
     * @param $searchCondition
     * @param int $limit
     * @param int $offset
     * @param string $order
     * @return mixed
     */
    public function searchWithCompany(array $searchCondition = [], int $limit = 0, int $offset = 0, array $orderBy = []);

    public function countSearchWithCompany(array $searchCondition = []);

    public function checkRegisterPhonePassword($phone, $keyRandomString);

    public function listCompanyByIds($ids);
}