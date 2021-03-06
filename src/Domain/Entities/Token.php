<?php

declare(strict_types=1);

namespace AbterPhp\Admin\Domain\Entities;

use AbterPhp\Framework\Domain\Entities\IStringerEntity;
use DateTimeImmutable;

class Token implements IStringerEntity
{
    protected string $id;

    protected string $apiClientId;

    protected DateTimeImmutable $expiresAt;

    protected ?DateTimeImmutable $revokedAt;

    /** @var string[] */
    protected array $adminResources;

    /**
     * Token constructor.
     *
     * @param string                 $id
     * @param string                 $apiClientId
     * @param DateTimeImmutable      $expiresAt
     * @param DateTimeImmutable|null $revokedAt
     * @param string[]               $adminResources
     */
    public function __construct(
        string $id,
        string $apiClientId,
        DateTimeImmutable $expiresAt,
        ?DateTimeImmutable $revokedAt,
        array $adminResources = []
    ) {
        $this->id             = $id;
        $this->apiClientId    = $apiClientId;
        $this->expiresAt      = $expiresAt;
        $this->revokedAt      = $revokedAt;
        $this->adminResources = $adminResources;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getApiClientId(): string
    {
        return $this->apiClientId;
    }

    /**
     * @param string $apiClientId
     */
    public function setApiClientId(string $apiClientId): Token
    {
        $this->apiClientId = $apiClientId;

        return $this;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    /**
     * @param DateTimeImmutable $expiresAt
     */
    public function setExpiresAt(DateTimeImmutable $expiresAt): Token
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getRevokedAt(): ?DateTimeImmutable
    {
        return $this->revokedAt;
    }

    /**
     * @param DateTimeImmutable|null $revokedAt
     */
    public function setRevokedAt(?DateTimeImmutable $revokedAt): Token
    {
        $this->revokedAt = $revokedAt;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getAdminResources(): array
    {
        return $this->adminResources;
    }

    /**
     * @param string[] $adminResources
     */
    public function setAdminResources(array $adminResources): void
    {
        $this->adminResources = $adminResources;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getId();
    }

    /**
     * @return array|null
     */
    public function toData(): ?array
    {
        return [
            "id" => $this->getId(),
        ];
    }

    /**
     * @return string
     */
    public function toJSON(): string
    {
        return json_encode($this->toData());
    }
}
