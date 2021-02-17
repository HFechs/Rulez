<?php
/*
 * This file is part of the Rulez library (https://github.com/HFechs/Rulez)
 *
 * Copyright (c) 2021 Václav Švirga (HFechs)
 *
 * For the full copyright and license information, please view the file
 * license.txt that was distributed with this source code.
 */

declare(strict_types=1);

namespace HFechs\Rulez\Cache;

use HFechs\Rulez\Defs\IRole;
use HFechs\Rulez\Defs\IRight;

/**
 * Right cache.
 */
class RightCache implements IRightCache
{
    /** @var IStorage */
    protected $storage;

    /** @var ?callable(?IRole $role): string */
    private $hashFromRoleCallback;

    public function __construct(IStorage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Load rights from cache.
     * @param IRole|null $role
     * @param int|null $resourceType
     * @param bool $own
     * @return iterable<IRight> null if rights don't exist
     */
    public function loadRights(?IRole $role, ?int $resourceType = null, bool $own = false): ?iterable
    {
        $hash = $this->rightsHash($role, $resourceType, $own);
        return $this->storage->load($hash);
    }

    /**
     * Remove rights from cache.
     * @param IRole|null $role
     * @param int|null $resourceType
     * @param bool $own
     * @return void
     */
    public function removeRights(?IRole $role, ?int $resourceType = null, bool $own = false): void
    {
        $hash = $this->rightsHash($role, $resourceType, $own);
        $this->storage->remove($hash);
    }

    /**
     * Save rights to cache (update if exist).
     * @param iterable<IRight> $rights
     * @param IRole|null $role
     * @param int|null $resourceType
     * @param bool $own
     * @return void
     */
    public function saveRights(iterable $rights, ?IRole $role, ?int $resourceType = null, bool $own = false): void
    {
        $hash = $this->rightsHash($role, $resourceType, $own);
        $this->storage->save($rights, $hash);
    }

    /**
     * Load superior resource types from cache.
     * @param int $resourceType
     * @return int[]
     */
    public function loadSuperiorResourceTypes(int $resourceType = null): ?iterable
    {
        $hash = $this->resourcesHash($resourceType);
        return $this->storage->load($hash);
    }

    /**
     * Remove superior resource types from cache.
     * @param int $resourceType
     * @return void
     */
    public function removeSuperiorResourceTypes(int $resourceType = null): void
    {
        $hash = $this->resourcesHash($resourceType);
        $this->storage->remove($hash);
    }

    /**
     * Save superior resource types to cache (update if exist).
     * @param int[] $resources
     * @param int $resourceType
     * @return void
     */
    public function saveSuperiorResourceTypes(iterable $resources, int $resourceType): void
    {
        $hash = $this->resourcesHash($resourceType);
        $this->storage->save($resources, $hash);
    }

    /**
     * Set hash from role callback.
     */
    public function setHashFromRoleCallback(?callable $callable): void
    {
        $this->hashFromRoleCallback = $callable;
    }

    protected function hashFromRole(?IRole $role): string
    {
        if ($this->hashFromRoleCallback) {
            $callback = $this->hashFromRoleCallback;
            return $callback($role);
        }

        return $role ? spl_object_hash($role) : "";
    }

    private function rightsHash(?IRole $role, ?int $resourceType = null, bool $own)
    {
        $hash = 'rights_r_'.
            $this->hashFromRole($role)."_".
            ((string) $resourceType)."_".
            ($own ? '1' : '0');
        return $hash;
    }

    private function resourcesHash(int $resourceType)
    {
        $hash = 'rights_sr_'.((string) $resourceType);
        return $hash;
    }
}