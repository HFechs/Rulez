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
 * Role cache interface.
 */

interface IRoleCache
{
    public function __construct(IStorage $storage);

    /**
     * Load roles from cache.
     * @param IUser $user
     * @param ?IResource $resource
     * @return iterable<IRole> null if roles don't exist
     */
    public function load(IUser $user, ?IResource $resource): ?iterable;

    /**
     * Remove roles from cache.
     * @param IUser $user
     * @param ?IResource $resource
     * @return void
     */
    public function remove(IUser $user, ?IResource $resource): void;

    /**
     * Save roles to cache (update if exist).
     * @param iterable<IRole> $user
     * @param IUser $user
     * @param ?IResource $resource
     * @return void
     */
    public function save(iterable $roles, IUser $user, ?IResource $resource);
}