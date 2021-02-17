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
 * Result cache interface.
 */
interface IResultCache
{
    public function __construct(IStorage $storage);

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
    ): ?bool;

    /**
     * Remove result from cache.
     * @param int $right IRight::R_*
     * @param IResource|int|null $resource IResource or resource type
     * @param IUser $user
     * @param IResource|null $parentResource
     */
    public function remove(
        int $right,
        $resource,
        IUser $user,
        ?IResource $parentResource = null
    ): void;

    /**
     * Save result to cache (update if exists).
     * @param int $right IRight::R_*
     * @param IResource|int|null $resource IResource or resource type
     * @param IUser $user
     * @param IResource|null $parentResource
     */
    public function save(
        bool $value,
        int $right,
        $resource,
        IUser $user,
        ?IResource $parentResource = null
    ): void;
}