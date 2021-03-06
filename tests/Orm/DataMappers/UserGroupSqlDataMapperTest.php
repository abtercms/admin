<?php

declare(strict_types=1);

namespace AbterPhp\Admin\Orm\DataMappers;

use AbterPhp\Admin\Domain\Entities\AdminResource;
use AbterPhp\Admin\Domain\Entities\UserGroup;
use AbterPhp\Admin\TestCase\Orm\DataMapperTestCase;
use AbterPhp\Admin\TestDouble\Orm\MockIdGeneratorFactory;
use AbterPhp\Framework\Domain\Entities\IStringerEntity;
use AbterPhp\Framework\TestDouble\Database\MockStatementFactory;
use PHPUnit\Framework\MockObject\MockObject;

class UserGroupSqlDataMapperTest extends DataMapperTestCase
{
    /** @var UserGroupSqlDataMapper */
    protected $sut;

    public function setUp(): void
    {
        parent::setUp();

        $this->sut = new UserGroupSqlDataMapper($this->readConnectionMock, $this->writeConnectionMock);
    }

    public function testAddWithoutRelated()
    {
        $nextId     = 'dab4e209-8f21-4421-955a-f83fc1527238';
        $identifier = 'foo';
        $name       = 'bar';

        $sql0       = 'INSERT INTO user_groups (id, identifier, name) VALUES (?, ?, ?)'; // phpcs:ignore
        $values     = [[$nextId, \PDO::PARAM_STR], [$identifier, \PDO::PARAM_STR], [$name, \PDO::PARAM_STR]];
        $statement0 = MockStatementFactory::createWriteStatement($this, $values);

        $this->writeConnectionMock
            ->expects($this->once())
            ->method('prepare')
            ->with($sql0)
            ->willReturn($statement0);

        $entity = new UserGroup($nextId, $identifier, $name);
        $this->sut->add($entity);

        $this->assertSame($nextId, $entity->getId());
    }

    public function testAddWithRelated()
    {
        $nextId         = '4675ecf5-593d-4568-9ff9-434ca25db5a7';
        $identifier     = 'foo';
        $name           = 'bar';
        $ugarId0        = '7f08b114-3a04-415a-8365-9e67d4a50cea';
        $ugarId1        = '6bd44298-7319-4428-b2b2-29c3d4652f39';
        $adminResources = [
            new AdminResource('a2e64f70-1914-402a-8d49-6d15abb62462', ''),
            new AdminResource('58d491ac-2742-401a-85f2-e05470dd1879', ''),
        ];

        $this->sut->setIdGenerator(MockIdGeneratorFactory::create($this, $ugarId0, $ugarId1));

        $sql0       = 'INSERT INTO user_groups (id, identifier, name) VALUES (?, ?, ?)'; // phpcs:ignore
        $values0    = [[$nextId, \PDO::PARAM_STR], [$identifier, \PDO::PARAM_STR], [$name, \PDO::PARAM_STR]];
        $statement0 = MockStatementFactory::createWriteStatement($this, $values0);

        $sql1       = 'INSERT INTO user_groups_admin_resources (id, user_group_id, admin_resource_id) VALUES (?, ?, ?)'; // phpcs:ignore
        $values1    = [
            [$ugarId0, \PDO::PARAM_STR],
            [$nextId, \PDO::PARAM_STR],
            [$adminResources[0]->getId(), \PDO::PARAM_STR],
        ];
        $statement1 = MockStatementFactory::createWriteStatement($this, $values1);

        $sql2       = 'INSERT INTO user_groups_admin_resources (id, user_group_id, admin_resource_id) VALUES (?, ?, ?)'; // phpcs:ignore
        $values2    = [
            [$ugarId1, \PDO::PARAM_STR],
            [$nextId, \PDO::PARAM_STR],
            [$adminResources[1]->getId(), \PDO::PARAM_STR],
        ];
        $statement2 = MockStatementFactory::createWriteStatement($this, $values2);

        $this->writeConnectionMock
            ->expects($this->exactly(3))
            ->method('prepare')
            ->withConsecutive([$sql0], [$sql1], [$sql2])
            ->willReturnOnConsecutiveCalls($statement0, $statement1, $statement2);

        $entity = new UserGroup($nextId, $identifier, $name, $adminResources);
        $this->sut->add($entity);

        $this->assertSame($nextId, $entity->getId());
    }

    public function testDelete()
    {
        $id         = '7f1b4ea5-6adf-49d8-98a7-9dc820ee8c97';
        $identifier = 'foo';
        $name       = 'bar';

        $sql0       = 'DELETE FROM user_groups_admin_resources WHERE (user_group_id = ?)'; // phpcs:ignore
        $values0    = [[$id, \PDO::PARAM_STR]];
        $statement0 = MockStatementFactory::createWriteStatement($this, $values0);

        $sql1       = 'UPDATE user_groups AS user_groups SET deleted_at = NOW() WHERE (id = ?)'; // phpcs:ignore
        $values1    = [[$id, \PDO::PARAM_STR]];
        $statement1 = MockStatementFactory::createWriteStatement($this, $values1);

        $this->writeConnectionMock
            ->expects($this->exactly(2))
            ->method('prepare')
            ->withConsecutive([$sql0], [$sql1])
            ->willReturnOnConsecutiveCalls($statement0, $statement1);

        $entity = new UserGroup($id, $identifier, $name);
        $this->sut->delete($entity);
    }

    public function testGetAll()
    {
        $id         = 'bde8a749-b409-43c6-a061-c6a7d2dce6a0';
        $identifier = 'foo';
        $name       = 'bar';

        $sql0         = 'SELECT ug.id, ug.identifier, ug.name, GROUP_CONCAT(ugar.admin_resource_id) AS admin_resource_ids FROM user_groups AS ug LEFT JOIN user_groups_admin_resources AS ugar ON ugar.user_group_id = ug.id WHERE (ug.deleted_at IS NULL) GROUP BY ug.id'; // phpcs:ignore
        $values       = [];
        $expectedData = [
            [
                'id'                 => $id,
                'identifier'         => $identifier,
                'name'               => $name,
                'admin_resource_ids' => 'b7853a53-6187-429a-828e-638cdd9e1381,704ab848-4ac6-460d-b31c-aeafc9faa0ab',
                // phpcs:ignore
            ],
        ];
        $statement0   = MockStatementFactory::createReadStatement($this, $values, $expectedData);

        $this->readConnectionMock
            ->expects($this->once())
            ->method('prepare')
            ->with($sql0)
            ->willReturn($statement0);

        $actualResult = $this->sut->getAll();

        $this->assertCollection($expectedData, $actualResult);
    }

    public function testGetPage()
    {
        $id         = 'bde8a749-b409-43c6-a061-c6a7d2dce6a0';
        $identifier = 'foo';
        $name       = 'bar';

        $sql0         = 'SELECT SQL_CALC_FOUND_ROWS ug.id, ug.identifier, ug.name, GROUP_CONCAT(ugar.admin_resource_id) AS admin_resource_ids FROM user_groups AS ug LEFT JOIN user_groups_admin_resources AS ugar ON ugar.user_group_id = ug.id WHERE (ug.deleted_at IS NULL) GROUP BY ug.id ORDER BY ug.created_at ASC LIMIT 10 OFFSET 0'; // phpcs:ignore
        $values       = [];
        $expectedData = [['id' => $id, 'identifier' => $identifier, 'name' => $name]];
        $statement0   = MockStatementFactory::createReadStatement($this, $values, $expectedData);

        $this->readConnectionMock
            ->expects($this->once())
            ->method('prepare')
            ->with($sql0)
            ->willReturn($statement0);

        $actualResult = $this->sut->getPage(0, 10, [], [], []);

        $this->assertCollection($expectedData, $actualResult);
    }

    public function testGetPageWithOrdersAndConditions()
    {
        $id         = 'bde8a749-b409-43c6-a061-c6a7d2dce6a0';
        $identifier = 'foo';
        $name       = 'bar';

        $orders     = ['ug.name ASC'];
        $conditions = ['ug.name = \'abc\'', 'abc.name = \'bca\''];

        $sql0         = 'SELECT SQL_CALC_FOUND_ROWS ug.id, ug.identifier, ug.name, GROUP_CONCAT(ugar.admin_resource_id) AS admin_resource_ids FROM user_groups AS ug LEFT JOIN user_groups_admin_resources AS ugar ON ugar.user_group_id = ug.id WHERE (ug.deleted_at IS NULL) AND (ug.name = \'abc\') AND (abc.name = \'bca\') GROUP BY ug.id ORDER BY ug.name ASC LIMIT 10 OFFSET 0'; // phpcs:ignore
        $values       = [];
        $expectedData = [['id' => $id, 'identifier' => $identifier, 'name' => $name]];
        $statement0   = MockStatementFactory::createReadStatement($this, $values, $expectedData);

        $this->readConnectionMock
            ->expects($this->once())
            ->method('prepare')
            ->with($sql0)
            ->willReturn($statement0);

        $actualResult = $this->sut->getPage(0, 10, $orders, $conditions, []);

        $this->assertCollection($expectedData, $actualResult);
    }

    public function testGetById()
    {
        $id         = '35ccea14-6a34-4fcf-a303-9bb8c827ff16';
        $identifier = 'foo';
        $name       = 'bar';

        $sql0         = 'SELECT ug.id, ug.identifier, ug.name, GROUP_CONCAT(ugar.admin_resource_id) AS admin_resource_ids FROM user_groups AS ug LEFT JOIN user_groups_admin_resources AS ugar ON ugar.user_group_id = ug.id WHERE (ug.deleted_at IS NULL) AND (ug.id = :user_group_id) GROUP BY ug.id'; // phpcs:ignore
        $values       = ['user_group_id' => [$id, \PDO::PARAM_STR]];
        $expectedData = [['id' => $id, 'identifier' => $identifier, 'name' => $name]];
        $statement0   = MockStatementFactory::createReadStatement($this, $values, $expectedData);

        $this->readConnectionMock
            ->expects($this->once())
            ->method('prepare')
            ->with($sql0)
            ->willReturn($statement0);

        $actualResult = $this->sut->getById($id);

        $this->assertEntity($expectedData[0], $actualResult);
    }

    public function testGetByIdentifier()
    {
        $id         = 'cf2bca2a-7ef5-4e01-a95c-5b4d92186e35';
        $identifier = 'foo';
        $name       = 'bar';

        $sql0         = 'SELECT ug.id, ug.identifier, ug.name, GROUP_CONCAT(ugar.admin_resource_id) AS admin_resource_ids FROM user_groups AS ug LEFT JOIN user_groups_admin_resources AS ugar ON ugar.user_group_id = ug.id WHERE (ug.deleted_at IS NULL) AND (ug.identifier = :identifier) GROUP BY ug.id'; // phpcs:ignore
        $values       = ['identifier' => [$identifier, \PDO::PARAM_STR]];
        $expectedData = [['id' => $id, 'identifier' => $identifier, 'name' => $name]];
        $statement0   = MockStatementFactory::createReadStatement($this, $values, $expectedData);

        $this->readConnectionMock
            ->expects($this->once())
            ->method('prepare')
            ->with($sql0)
            ->willReturn($statement0);

        $actualResult = $this->sut->getByIdentifier($identifier);

        $this->assertEntity($expectedData[0], $actualResult);
    }

    public function testUpdateWithoutRelated()
    {
        $id         = '368e8be3-58b2-4b60-8a43-5b98242e6716';
        $identifier = 'foo';
        $name       = 'bar';

        $sql0       = 'UPDATE user_groups AS user_groups SET identifier = ?, name = ? WHERE (id = ?) AND (deleted_at IS NULL)'; // phpcs:ignore
        $values0    = [[$identifier, \PDO::PARAM_STR], [$name, \PDO::PARAM_STR], [$id, \PDO::PARAM_STR]];
        $statement0 = MockStatementFactory::createWriteStatement($this, $values0);

        $sql1       = 'DELETE FROM user_groups_admin_resources WHERE (user_group_id = ?)'; // phpcs:ignore
        $values1    = [[$id, \PDO::PARAM_STR]];
        $statement1 = MockStatementFactory::createWriteStatement($this, $values1);

        $this->writeConnectionMock
            ->expects($this->exactly(2))
            ->method('prepare')
            ->withConsecutive([$sql0], [$sql1])
            ->willReturnOnConsecutiveCalls($statement0, $statement1);

        $entity = new UserGroup($id, $identifier, $name);
        $this->sut->update($entity);
    }

    public function testUpdateWithRelated()
    {
        $id             = 'e2c961a8-d844-4dbd-96eb-7fb603dcd6d7';
        $identifier     = 'foo';
        $name           = 'bar';
        $ugarId0        = '7f08b114-3a04-415a-8365-9e67d4a50cea';
        $ugarId1        = '6bd44298-7319-4428-b2b2-29c3d4652f39';
        $adminResources = [
            new AdminResource('aacc2773-9549-438e-8b43-b27236ca5c64', ''),
            new AdminResource('e87c7ab9-b86e-4de6-a4fe-8ad3486cd952', ''),
        ];

        $this->sut->setIdGenerator(MockIdGeneratorFactory::create($this, $ugarId0, $ugarId1));

        $sql0       = 'UPDATE user_groups AS user_groups SET identifier = ?, name = ? WHERE (id = ?) AND (deleted_at IS NULL)'; // phpcs:ignore
        $values0    = [[$identifier, \PDO::PARAM_STR], [$name, \PDO::PARAM_STR], [$id, \PDO::PARAM_STR]];
        $statement0 = MockStatementFactory::createWriteStatement($this, $values0);

        $sql1       = 'DELETE FROM user_groups_admin_resources WHERE (user_group_id = ?)'; // phpcs:ignore
        $values1    = [[$id, \PDO::PARAM_STR]];
        $statement1 = MockStatementFactory::createWriteStatement($this, $values1);

        $sql2       = 'INSERT INTO user_groups_admin_resources (id, user_group_id, admin_resource_id) VALUES (?, ?, ?)'; // phpcs:ignore
        $values2    = [
            [$ugarId0, \PDO::PARAM_STR],
            [$id, \PDO::PARAM_STR],
            [$adminResources[0]->getId(), \PDO::PARAM_STR],
        ];
        $statement2 = MockStatementFactory::createWriteStatement($this, $values2);

        $sql3       = 'INSERT INTO user_groups_admin_resources (id, user_group_id, admin_resource_id) VALUES (?, ?, ?)'; // phpcs:ignore
        $values3    = [
            [$ugarId1, \PDO::PARAM_STR],
            [$id, \PDO::PARAM_STR],
            [$adminResources[1]->getId(), \PDO::PARAM_STR],
        ];
        $statement3 = MockStatementFactory::createWriteStatement($this, $values3);

        $this->writeConnectionMock
            ->expects($this->exactly(4))
            ->method('prepare')
            ->withConsecutive([$sql0], [$sql1], [$sql2], [$sql3])
            ->willReturnOnConsecutiveCalls($statement0, $statement1, $statement2, $statement3);

        $entity = new UserGroup($id, $identifier, $name, $adminResources);
        $this->sut->update($entity);
    }

    public function testAddThrowsExceptionIfCalledWithInvalidEntity()
    {
        $this->expectException(\InvalidArgumentException::class);

        /** @var IStringerEntity|MockObject $entity */
        $entity = $this->createMock(IStringerEntity::class);

        $this->sut->add($entity);
    }

    public function testDeleteThrowsExceptionIfCalledWithInvalidEntity()
    {
        $this->expectException(\InvalidArgumentException::class);

        /** @var IStringerEntity|MockObject $entity */
        $entity = $this->createMock(IStringerEntity::class);

        $this->sut->delete($entity);
    }

    public function testUpdateThrowsExceptionIfCalledWithInvalidEntity()
    {
        $this->expectException(\InvalidArgumentException::class);

        /** @var IStringerEntity|MockObject $entity */
        $entity = $this->createMock(IStringerEntity::class);

        $this->sut->update($entity);
    }

    /**
     * @param array     $expectedData
     * @param UserGroup $entity
     */
    protected function assertEntity(array $expectedData, $entity)
    {
        $this->assertInstanceOf(UserGroup::class, $entity);
        $this->assertEquals($expectedData['id'], $entity->getId());
        $this->assertSame($expectedData['identifier'], $entity->getIdentifier());
        $this->assertSame($expectedData['name'], $entity->getName());

        if (!array_key_exists('admin_resource_ids', $expectedData)) {
            return;
        }

        $ids = [];
        foreach ($entity->getAdminResources() as $resource) {
            $ids[] = $resource->getId();
        }
        $this->assertSame($expectedData['admin_resource_ids'], implode(',', $ids));
    }
}
