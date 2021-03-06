<?php

declare(strict_types=1);

namespace AbterPhp\Admin\Service\Execute;

use AbterPhp\Admin\Domain\Entities\User as Entity;
use AbterPhp\Admin\Domain\Entities\UserGroup;
use AbterPhp\Admin\Domain\Entities\UserLanguage;
use AbterPhp\Admin\Orm\UserRepo as GridRepo;
use AbterPhp\Admin\Validation\Factory\User as ValidatorFactory;
use AbterPhp\Framework\Crypto\Crypto;
use AbterPhp\Framework\Domain\Entities\IStringerEntity;
use Opulence\Events\Dispatchers\IEventDispatcher;
use Opulence\Http\Requests\UploadedFile;
use Opulence\Orm\IUnitOfWork;
use Opulence\Validation\IValidator;

class User extends RepoServiceAbstract
{
    private Crypto $crypto;

    /**
     * User constructor.
     *
     * @param GridRepo                $repo
     * @param ValidatorFactory        $validatorFactory
     * @param IUnitOfWork             $unitOfWork
     * @param IEventDispatcher        $eventDispatcher
     * @param Crypto                  $crypto
     */
    public function __construct(
        GridRepo $repo,
        ValidatorFactory $validatorFactory,
        IUnitOfWork $unitOfWork,
        IEventDispatcher $eventDispatcher,
        Crypto $crypto
    ) {
        parent::__construct(
            $repo,
            $validatorFactory,
            $unitOfWork,
            $eventDispatcher
        );

        $this->crypto                  = $crypto;
    }

    /**
     * @param int $additionalData
     *
     * @return IValidator
     */
    protected function getValidator(int $additionalData): IValidator
    {
        if ($this->validator) {
            return $this->validator;
        }

        $this->validator = $this->validatorFactory->createValidator();

        return $this->validator;
    }

    /**
     * @param string $entityId
     *
     * @return Entity
     */
    public function createEntity(string $entityId): IStringerEntity
    {
        $userLanguage = new UserLanguage(
            '',
            '',
            ''
        );

        return new Entity(
            $entityId,
            '',
            '',
            '',
            false,
            false,
            $userLanguage
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param IStringerEntity $entity
     * @param array           $postData
     * @param UploadedFile[]  $fileData
     *
     * @return Entity
     */
    protected function fillEntity(IStringerEntity $entity, array $postData, array $fileData): IStringerEntity
    {
        assert($entity instanceof Entity, new \InvalidArgumentException('Invalid entity'));

        $username          = $postData['username'] ?: '';
        $email             = $postData['email'] ?: '';
        $password          = $postData['password'] ?: '';
        $isGravatarAllowed = $postData['is_gravatar_allowed'] ?? false;
        $canLogin          = $postData['can_login'] ?? false;
        $userLanguage      = $this->createUserLanguage($postData);
        $userGroups        = $this->createUserGroups($postData);

        $entity->setUsername($username)
            ->setEmail($email)
            ->setIsGravatarAllowed((bool)$isGravatarAllowed)
            ->setCanLogin((bool)$canLogin)
            ->setUserLanguage($userLanguage)
            ->setUserGroups($userGroups);

        if ($password) {
            $entity->setPassword($this->crypto->hashCrypt($password));
        }

        return $entity;
    }

    /**
     * @param array $postData
     *
     * @return UserLanguage
     */
    protected function createUserLanguage(array $postData): UserLanguage
    {
        $userLanguageId = $postData['user_language_id'] ?? '';

        return new UserLanguage($userLanguageId, '', '');
    }

    /**
     * @param array $postData
     *
     * @return array
     */
    protected function createUserGroups(array $postData): array
    {
        $userGroups = [];
        if (!empty($postData['user_group_ids'])) {
            foreach ($postData['user_group_ids'] as $userGroupId) {
                $userGroups[] = new UserGroup(
                    (string)$userGroupId,
                    '',
                    ''
                );
            }
        }

        return $userGroups;
    }
}
