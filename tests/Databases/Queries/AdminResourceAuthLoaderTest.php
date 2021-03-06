<?php

declare(strict_types=1);

namespace AbterPhp\Admin\Databases\Queries;

use AbterPhp\Admin\Exception\Database;
use AbterPhp\Framework\TestCase\Database\QueryTestCase;
use AbterPhp\Framework\TestDouble\Database\MockStatementFactory;

class AdminResourceAuthLoaderTest extends QueryTestCase
{
    /** @var AdminResourceAuthLoader - System Under Test */
    protected $sut;

    public function setUp(): void
    {
        parent::setUp();

        $this->sut = new AdminResourceAuthLoader($this->connectionPoolMock);
    }

    public function testLoadAll()
    {
        $userGroupIdentifier     = 'foo';
        $adminResourceIdentifier = 'bar';

        $sql0         = 'SELECT ug.identifier AS v0, ar.identifier AS v1 FROM user_groups_admin_resources AS ugar INNER JOIN admin_resources AS ar ON ugar.admin_resource_id = ar.id AND ar.deleted_at IS NULL INNER JOIN user_groups AS ug ON ugar.user_group_id = ug.id AND ug.deleted_at IS NULL'; // phpcs:ignore
        $valuesToBind = [];
        $returnValues = [
            [
                'v0' => $userGroupIdentifier,
                'v1' => $adminResourceIdentifier,
            ],
        ];
        $statement0   = MockStatementFactory::createReadStatement($this, $valuesToBind, $returnValues);

        $this->readConnectionMock
            ->expects($this->once())
            ->method('prepare')
            ->withConsecutive([$sql0])
            ->willReturnOnConsecutiveCalls($statement0);

        $actualResult = $this->sut->loadAll();

        $this->assertEquals($returnValues, $actualResult);
    }

    public function testLoadAllThrowsExceptionIfQueryFails()
    {
        $errorInfo = ['FOO123', 1, 'near AS v0, ar.identifier: hello'];

        $this->expectException(Database::class);
        $this->expectExceptionCode($errorInfo[1]);

        $sql0         = 'SELECT ug.identifier AS v0, ar.identifier AS v1 FROM user_groups_admin_resources AS ugar INNER JOIN admin_resources AS ar ON ugar.admin_resource_id = ar.id AND ar.deleted_at IS NULL INNER JOIN user_groups AS ug ON ugar.user_group_id = ug.id AND ug.deleted_at IS NULL'; // phpcs:ignore
        $valuesToBind = [];
        $statement0   = MockStatementFactory::createErrorStatement($this, $valuesToBind, $errorInfo);

        $this->readConnectionMock
            ->expects($this->exactly(1))
            ->method('prepare')
            ->withConsecutive([$sql0])
            ->willReturnOnConsecutiveCalls($statement0);

        $this->sut->loadAll();
    }
}
