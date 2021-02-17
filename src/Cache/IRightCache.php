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
 * Right cache interface.
 */
interface IRightCache
{
    public function __construct(IStorage $storage);

    /**
     * Load rights from cache.
     * @param IRole|null $role
     * @param int|null $resourceType
     * @param bool $own
     * @return iterable<IRight> null if rights don't exist
     */
    public function loadRights(?IRole $role, ?int $resourceType = null, bool $own = false): ?iterable;

    /**
     * Remove rights from cache.
     * @param IRole|null $role
     * @param int|null $resourceType
     * @param bool $own
     * @return void
     */
    public function removeRights(?IRole $role, ?int $resourceType = null, bool $own = false): void;

    /**
     * Save rights to cache (update if exist).
     * @param iterable<IRight> $rights
     * @param IRole|null $role
     * @param int|null $resourceType
     * @param bool $own
     * @return void
     */
    public function saveRights(iterable $rights, ?IRole $role, ?int $resourceType = null, bool $own = false): void;

    /**
     * Load superior resource types from cache.
     * @param int $resourceType
     * @return int[]
     */
    public function loadSuperiorResourceTypes(int $resourceType = null): ?iterable;

    /**
     * Remove superior resource types from cache.
     * @param int $resourceType
     * @return void
     */
    public function removeSuperiorResourceTypes(int $resourceType = null): void;

    /**
     * Save superior resource types to cache (update if exist).
     * @param int[] $resources
     * @param int $resourceType
     * @return void
     */
    public function saveSuperiorResourceTypes(iterable $resources, int $resourceType): void;
}