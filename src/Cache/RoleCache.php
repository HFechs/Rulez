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

use HFechs\Rulez\Defs\IResource;
use HFechs\Rulez\Defs\IRole;
use HFechs\Rulez\Defs\IUser;
use HFechs\Rulez\Defs\IRight;

/**
 * Role cache.
 */

class RoleCache implements IRoleCache
{
    /** @var IStorage */
    protected $storage;

    /** @var ?callable(IResource|int|null $resource): string */
    private $hashFromUserCallback;

    /** @var ?callable(IUser $user): string */
    private $hashFromResourceCallback;

    public function __construct(IStorage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Load roles from cache.
     * @param IUser $user
     * @param ?IResource $resource
     * @return iterable<IRole> null if roles don't exist
     */
    public function load(IUser $user, ?IResource $resource): ?iterable
    {
        $hash = $this->hash($user, $resource);
        return $this->storage->load($hash);
    }

    /**
     * Remove roles from cache.
     * @param IUser $user
     * @param ?IResource $resource
     * @return void
     */
    public function remove(IUser $user, ?IResource $resource): void
    {
        $hash = $this->hash($user, $resource);
        $this->storage->remove($hash);
    }

    /**
     * Save roles to cache (update if exist).
     * @param iterable<IRole> $user
     * @param IUser $user
     * @param ?IResource $resource
     * @return void
     */
    public function save(iterable $roles, IUser $user, ?IResource $resource)
    {
        $hash = $this->hash($user, $resource);
        $this->storage->save($roles, $hash);
    }

    /**
     * Set hash from resource callback.
     */
    public function setHashFromResourceCallback(callable $callable): void
    {
        $this->hashFromResourceCallback = $callable;
    }

    /**
     * Set hash from user callback.
     */
    public function setHashFromUserCallback(callable $callable): void
    {
        $this->hashFromUserCallback = $callable;
    }

    protected function hashFromResource($resource): string
    {
        if ($this->hashFromResourceCallback) {
            $callback = $this->hashFromResourceCallback;
            return $callback($resource);
        }

        return $resource ? spl_object_hash($resource) : "";
    }

    protected function hashFromUser(IUser $user): string
    {
        if ($this->hashFromUserCallback) {
            $callback = $this->hashFromUserCallback;
            return $callback($user);
        }

        return spl_object_hash($user);
    }

    private function hash(IUser $user, ?IResource $resource): string
    {
        $hash = 'roles_'.
            $this->hashFromResource($resource)."_".
            $this->hashFromUser($user);
        return $hash;
    }
}