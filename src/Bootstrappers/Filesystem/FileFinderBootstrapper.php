<?php

declare(strict_types=1);

namespace AbterPhp\Admin\Bootstrappers\Filesystem;

use AbterPhp\Framework\Constant\Env;
use AbterPhp\Framework\Filesystem\FileFinder;
use AbterPhp\Framework\Filesystem\IFileFinder;
use AbterPhp\Framework\Module\Manager; // @phan-suppress-current-line PhanUnreferencedUseNormal
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Opulence\Databases\Adapters\Pdo\MySql\Driver as MySqlDriver;
use Opulence\Databases\Adapters\Pdo\PostgreSql\Driver as PostgreSqlDriver;
use Opulence\Environments\Environment;
use Opulence\Ioc\Bootstrappers\Bootstrapper;
use Opulence\Ioc\Bootstrappers\ILazyBootstrapper;
use Opulence\Ioc\IContainer;

class FileFinderBootstrapper extends Bootstrapper implements ILazyBootstrapper
{
    const MIGRATION_FILE_FINDER = 'MigrationFileFinder';

    /** @var string|null */
    protected $dbDriverName;

    /**
     * @return array
     */
    public function getBindings(): array
    {
        return [
            IFileFinder::class,
            static::MIGRATION_FILE_FINDER,
        ];
    }

    /**
     * @param IContainer $container
     *
     * @throws \Opulence\Ioc\IocException
     */
    public function registerBindings(IContainer $container)
    {
        $this->registerDefaultFileFinder($container);
        $this->registerMigrationFileFinder($container);
    }

    /**
     * @param IContainer $container
     */
    protected function registerDefaultFileFinder(IContainer $container)
    {
        /** @var IFileFinder $fileFinder */
        $fileFinder = new FileFinder();

        $container->bindInstance(IFileFinder::class, $fileFinder);
    }

    /**
     * @param IContainer $container
     */
    protected function registerMigrationFileFinder(IContainer $container)
    {
        /** @var Manager $abterModuleManager */
        global $abterModuleManager;

        $dbDriver   = $this->getDbDriver();
        $fileFinder = new FileFinder();
        foreach ($abterModuleManager->getResourcePaths() as $resourcePath) {
            $path    = sprintf('%s%s%s', $resourcePath, DIRECTORY_SEPARATOR, $dbDriver);
            $adapter = new Local($path);
            $fs      = new Filesystem($adapter);

            $fileFinder->registerFilesystem($fs);
        }

        $container->bindInstance(static::MIGRATION_FILE_FINDER, $fileFinder);
    }

    /**
     * @return string
     */
    protected function getDbDriver(): string
    {
        if ($this->dbDriverName !== null) {
            return $this->dbDriverName;
        }

        $driverClass = Environment::getVar(Env::DB_DRIVER) ?: PostgreSqlDriver::class;

        switch ($driverClass) {
            case MySqlDriver::class:
                $this->dbDriverName = 'mysql';
                break;
            case PostgreSqlDriver::class:
                $this->dbDriverName = 'pgsql';
                break;
            default:
                throw new \RuntimeException(
                    "Invalid database driver type specified in environment var \"DB_DRIVER\": $driverClass"
                );
        }

        return $this->dbDriverName;
    }
}
