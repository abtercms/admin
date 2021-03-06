<?php

declare(strict_types=1);

namespace AbterPhp\Admin\Orm\DataMappers;

use AbterPhp\Admin\Domain\Entities\UserLanguage;
use AbterPhp\Admin\TestCase\Orm\DataMapperTestCase;
use AbterPhp\Framework\Domain\Entities\IStringerEntity;
use AbterPhp\Framework\TestDouble\Database\MockStatementFactory;
use PHPUnit\Framework\MockObject\MockObject;

class UserLanguageSqlDataMapperTest extends DataMapperTestCase
{
    /** @var UserLanguageSqlDataMapper */
    protected $sut;

    public function setUp(): void
    {
        parent::setUp();

        $this->sut = new UserLanguageSqlDataMapper($this->readConnectionMock, $this->writeConnectionMock);
    }

    public function testAdd()
    {
        $nextId     = '9b6ae58b-1aff-4344-a2ae-cda43a40674e';
        $identifier = 'foo';
        $name       = 'Foo';

        $sql0       = 'INSERT INTO user_languages (id, identifier, name) VALUES (?, ?, ?)'; // phpcs:ignore
        $values     = [[$nextId, \PDO::PARAM_STR], [$identifier, \PDO::PARAM_STR], [$name, \PDO::PARAM_STR]];
        $statement0 = MockStatementFactory::createWriteStatement($this, $values);

        $this->writeConnectionMock
            ->expects($this->once())
            ->method('prepare')
            ->with($sql0)
            ->willReturn($statement0);

        $entity = new UserLanguage($nextId, $identifier, $name);

        $this->sut->add($entity);

        $this->assertSame($nextId, $entity->getId());
    }

    public function testDelete()
    {
        $id         = '8fe2f659-dbe5-4995-9e07-f49fb018cfe7';
        $identifier = 'foo';
        $name       = 'Foo';

        $sql0       = 'UPDATE user_languages AS user_languages SET deleted_at = NOW() WHERE (id = ?)'; // phpcs:ignore
        $values     = [[$id, \PDO::PARAM_STR]];
        $statement0 = MockStatementFactory::createWriteStatement($this, $values);

        $this->writeConnectionMock
            ->expects($this->once())
            ->method('prepare')
            ->with($sql0)
            ->willReturn($statement0);

        $entity = new UserLanguage($id, $identifier, $name);

        $this->sut->delete($entity);
    }

    public function testGetAll()
    {
        $id0         = '24bd4165-1229-4a6e-a679-76bf90743ee1';
        $identifier0 = 'foo';
        $name0       = 'Foo';
        $id1         = '51eac0fc-2b26-4231-9559-469e59fae694';
        $identifier1 = 'bar';
        $name1       = 'Bar';

        $sql0         = 'SELECT user_languages.id, user_languages.identifier, user_languages.name FROM user_languages WHERE (user_languages.deleted_at IS NULL)'; // phpcs:ignore
        $values       = [];
        $expectedData = [
            ['id' => $id0, 'identifier' => $identifier0, 'name' => $name0],
            ['id' => $id1, 'identifier' => $identifier1, 'name' => $name1],
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

    public function testGetPageWithOrdersAndConditions()
    {
        $id0         = '24bd4165-1229-4a6e-a679-76bf90743ee1';
        $identifier0 = 'foo';
        $name0       = 'Foo';
        $id1         = '51eac0fc-2b26-4231-9559-469e59fae694';
        $identifier1 = 'bar';
        $name1       = 'Bar';

        $orders     = ['ac.description ASC'];
        $conditions = ['ac.description LIKE \'abc%\'', 'abc.description LIKE \'%bca\''];

        $sql0         = 'SELECT SQL_CALC_FOUND_ROWS user_languages.id, user_languages.identifier, user_languages.name FROM user_languages WHERE (user_languages.deleted_at IS NULL) AND (ac.description LIKE \'abc%\') AND (abc.description LIKE \'%bca\') ORDER BY ac.description ASC LIMIT 10 OFFSET 0'; // phpcs:ignore
        $values       = [];
        $expectedData = [
            ['id' => $id0, 'identifier' => $identifier0, 'name' => $name0],
            ['id' => $id1, 'identifier' => $identifier1, 'name' => $name1],
        ];
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
        $id         = '24bd4165-1229-4a6e-a679-76bf90743ee1';
        $identifier = 'foo';
        $name       = 'Foo';

        $sql0         = 'SELECT user_languages.id, user_languages.identifier, user_languages.name FROM user_languages WHERE (user_languages.deleted_at IS NULL) AND (user_languages.id = :user_language_id)'; // phpcs:ignore
        $values       = ['user_language_id' => [$id, \PDO::PARAM_STR]];
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
        $id         = '24bd4165-1229-4a6e-a679-76bf90743ee1';
        $identifier = 'foo';
        $name       = 'Foo';

        $sql0         = 'SELECT user_languages.id, user_languages.identifier, user_languages.name FROM user_languages WHERE (user_languages.deleted_at IS NULL) AND (identifier = :identifier)'; // phpcs:ignore
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

    public function testUpdate()
    {
        $id         = '24bd4165-1229-4a6e-a679-76bf90743ee1';
        $identifier = 'foo';
        $name       = 'Foo';

        $sql0       = 'UPDATE user_languages AS user_languages SET identifier = ?, name = ? WHERE (id = ?) AND (deleted_at IS NULL)'; // phpcs:ignore
        $values     = [[$identifier, \PDO::PARAM_STR], [$name, \PDO::PARAM_STR], [$id, \PDO::PARAM_STR]];
        $statement0 = MockStatementFactory::createWriteStatement($this, $values);

        $this->writeConnectionMock
            ->expects($this->once())
            ->method('prepare')
            ->with($sql0)
            ->willReturn($statement0);

        $entity = new UserLanguage($id, $identifier, $name);

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
     * @param array        $expectedData
     * @param UserLanguage $entity
     */
    protected function assertEntity(array $expectedData, $entity)
    {
        $this->assertInstanceOf(UserLanguage::class, $entity);
    }
}
