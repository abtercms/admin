<?php

declare(strict_types=1);

namespace AbterPhp\Admin\Orm;

use AbterPhp\Admin\Domain\Entities\Token as Entity;
use AbterPhp\Admin\Orm\DataMappers\TokenSqlDataMapper; // @phan-suppress-current-line PhanUnreferencedUseNormal
use Opulence\Orm\OrmException;
use Opulence\Orm\Repositories\Repository;

class TokenRepo extends Repository
{
    /**
     * @param string $username
     *
     * @return Entity|null
     * @throws OrmException
     */
    public function getByClientId(string $username): ?Entity
    {
        /** @see TokenSqlDataMapper::getByClientId() */
        return $this->getFromDataMapper('getByClientId', [$username]);
    }
}
