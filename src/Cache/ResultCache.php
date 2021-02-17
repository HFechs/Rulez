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
use HFechs\Rulez\Defs\IUser;
use HFechs\Rulez\Defs\IRight;

/**
 * Result cache.
 */
class ResultCache implements IResultCache
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
     * Load result from cache.
     * @param int $right IRight::R_*
     * @param IResource|int|null $resource IResource or resource type
     * @param IUser $user
     * @param IResource|null $parentResource
     * @return ?bool null if result doesn't exist
     */
    public function load(
        int $right,
        $resource,
        IUser $user,
        ?IResource $parentResource = null
    ): ?bool
    {
        $hash = $this->hash($right, $resource, $user, $parentResource);
        return $this->storage->load($hash);
    }

    /**
     * Remove result from cache.
     * @param int $right IRight::R_*
     * @param IResource|int|null $resource IResource or resource type
     * @param IUser $user
     * @param IResource|null $parentResource
     * @return void
     */
    public function remove(
        int $right,
        $resource,
        IUser $user,
        ?IResource $parentResource = null
    ): void
    {
        $hash = $this->hash($right, $resource, $user, $parentResource);
        $this->storage->remove($hash);
    }

    /**
     * Save result to cache.
     * @param int $right IRight::R_*
     * @param IResource|int|null $resource IResource or resource type
     * @param IUser $user
     * @param IResource|null $parentResource
     * @return void
     */
    public function save(
        bool $value,
        int $right,
        $resource,
        IUser $user,
        ?IResource $parentResource = null
    ): void
    {
        $hash = $this->hash($right, $resource, $user, $parentResource);
        $this->storage->save($value, $hash);
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

        if ($resource === null) {
            return '';
        } elseif (is_int($resource)) {
            return (string) $resource;
        } else {
            return spl_object_hash($resource);
        }
    }

    protected function hashFromUser(IUser $user): string
    {
        if ($this->hashFromUserCallback) {
            $callback = $this->hashFromUserCallback;
            return $callback($user);
        }

        return spl_object_hash($user);
    }

    private function hash(
        int $right,
        $resource,
        IUser $user,
        ?IResource $parentResource
    ): string
    {
        $hash = 'result_'.
            ((string) $right)."_".
            $this->hashFromResource($resource)."_".
            $this->hashFromUser($user)."_".
            $this->hashFromResource($parentResource);
        return $hash;
    }
}