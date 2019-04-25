<?php

declare(strict_types=1);

namespace AbterPhp\Admin\Authorization;

use Casbin\Exceptions\CasbinException;
use Casbin\Model\Model;
use Casbin\Persist\Adapter as CasbinAdapter;
use AbterPhp\Framework\Databases\Queries\IAuthLoader;
use AbterPhp\Admin\Databases\Queries\UserAuthLoader;

class UserProvider implements CasbinAdapter
{
    /** @var IAuthLoader */
    protected $authQueries;

    /**
     * UserProvider constructor.
     *
     * @param UserAuthLoader $userAuth
     */
    public function __construct(UserAuthLoader $userAuth)
    {
        $this->authQueries = $userAuth;
    }

    /**
     * @param Model $model
     */
    public function loadPolicy($model)
    {
        $rawData = $this->authQueries->loadAll();

        foreach ($rawData as $line) {
            $model->model['g']['g']->policy[] = [$line['v0'], $line['v1'], '', '', ','];
        }

        return;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param Model $model
     *
     * @return bool
     */
    public function savePolicy($model)
    {
        return true;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param string $sec
     * @param string $ptype
     * @param array  $rule
     *
     * @return void
     */
    public function addPolicy($sec, $ptype, $rule)
    {
        return;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param string $sec
     * @param string $ptype
     * @param array  $rule
     *
     * @return int
     */
    public function removePolicy($sec, $ptype, $rule)
    {
        $count = 0;

        return $count;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param       $sec
     * @param       $ptype
     * @param       $fieldIndex
     * @param mixed ...$fieldValues
     *
     * @throws CasbinException
     */
    public function removeFilteredPolicy($sec, $ptype, $fieldIndex, ...$fieldValues)
    {
        throw new CasbinException('not implemented');
    }
}