<?php

declare(strict_types=1);

namespace AbterPhp\Admin\Http\Controllers\Api;

use AbterPhp\Admin\Service\Execute\User as RepoService;
use AbterPhp\Framework\Databases\Queries\FoundRows;
use AbterPhp\Framework\Http\Controllers\Admin\ApiAbstract;
use Opulence\Http\Responses\Response;
use Opulence\Routing\Controller;
use Psr\Log\LoggerInterface;

class User extends ApiAbstract
{
    const ENTITY_SINGULAR = 'user';
    const ENTITY_PLURAL   = 'users';

    /**
     * User constructor.
     *
     * @param LoggerInterface $logger
     * @param RepoService     $repoService
     * @param FoundRows       $foundRows
     */
    public function __construct(LoggerInterface $logger, RepoService $repoService, FoundRows $foundRows)
    {
        parent::__construct($logger, $repoService, $foundRows);
    }

    /**
     * @return array
     */
    public function getSharedData(): array
    {
        $data = $this->request->getJsonBody();

        if (array_key_exists('password', $data)) {
            $data['password_repeated'] = $data['password'];
        }

        return $data;
    }
}
