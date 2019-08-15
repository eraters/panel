<?php

namespace Pterodactyl\Services\Api;

use Illuminate\Support\Str;
use Pterodactyl\Models\ApiKey;
use Illuminate\Contracts\Encryption\Encrypter;
use Pterodactyl\Contracts\Repository\ApiKeyRepositoryInterface;

class KeyCreationService
{
    /**
     * @var \Illuminate\Contracts\Encryption\Encrypter
     */
    private $encrypter;

    /**
     * @var int
     */
    private $keyType = ApiKey::TYPE_NONE;

    /**
     * @var \Pterodactyl\Contracts\Repository\ApiKeyRepositoryInterface
     */
    private $repository;

    /**
     * ApiKeyService constructor.
     *
     * @param \Pterodactyl\Contracts\Repository\ApiKeyRepositoryInterface $repository
     * @param \Illuminate\Contracts\Encryption\Encrypter                  $encrypter
     */
    public function __construct(ApiKeyRepositoryInterface $repository, Encrypter $encrypter)
    {
        $this->encrypter = $encrypter;
        $this->repository = $repository;
    }

    /**
     * Set the type of key that should be created. By default an orphaned key will be
     * created. These keys cannot be used for anything, and will not render in the UI.
     *
     * @param int $type
     * @return \Pterodactyl\Services\Api\KeyCreationService
     */
    public function setKeyType(int $type)
    {
        $this->keyType = $type;

        return $this;
    }

    /**
     * Create a new API key for the Panel using the permissions passed in the data request.
     * This will automatically generate an identifier and an encrypted token that are
     * stored in the database.
     *
     * @param array $data
     * @param array $permissions
     * @return \Pterodactyl\Models\ApiKey
     *
     * @throws \Pterodactyl\Exceptions\Model\DataValidationException
     */
    public function handle(array $data, array $permissions = []): ApiKey
    {
        $data = array_merge($data, [
            'key_type' => $this->keyType,
            'identifier' => Str::random(ApiKey::IDENTIFIER_LENGTH),
            'token' => $this->encrypter->encrypt(Str::random(ApiKey::KEY_LENGTH)),
        ]);

        if ($this->keyType === ApiKey::TYPE_APPLICATION) {
            $data = array_merge($data, $permissions);
        }

        $instance = $this->repository->create($data, true, true);

        return $instance;
    }
}
