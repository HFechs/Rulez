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

namespace HFechs\Rulez;

use HFechs\Rulez\Authorizator\RolesAuthorizator;
use HFechs\Rulez\Authorizator\RoleAuthorizator;
use HFechs\Rulez\Authorizator\UserAuthorizator;
use HFechs\Rulez\Cache\IResultCache;
use HFechs\Rulez\Cache\IRightCache;
use HFechs\Rulez\Cache\IRoleCache;
use HFechs\Rulez\Defs\IResource;
use HFechs\Rulez\Defs\IResourceManager;
use HFechs\Rulez\Defs\IRight;
use HFechs\Rulez\Defs\IRightRepository;
use HFechs\Rulez\Defs\IRoleRepository;
use HFechs\Rulez\Defs\IUser;

/**
 * Rulez authorizator.
 */
class Rulez
{
    /** @var UserAuthorizator */
    protected $userAuthorizator;

    /** @var RolesAuthorizator */
    protected $rolesAuthorizator;

    /** @var RoleAuthorizator */
    protected $roleAuthorizator;

    /** @var ?IResultCache */
    protected $resultCache;

    public function __construct(
        IRightRepository $rightRepository,
        IRoleRepository $roleRepository,
        IResourceManager $resourceManager
    )
    {
        $this->roleAuthorizator = new RoleAuthorizator($rightRepository);
        $this->rolesAuthorizator = new RolesAuthorizator($this->roleAuthorizator);
        $this->userAuthorizator = new UserAuthorizator($this->rolesAuthorizator, $roleRepository, $resourceManager);
    }

    /**
     * Set result cache.
     */
    public function setResultCache(?IResultCache $cache): void
    {
        $this->resultCache = $cache;
    }

    /**
     * Get result cache.
     */
    public function getResultCache(): ?IResultCache
    {
        return $this->resultCache;
    }

    /**
     * Set role cache.
     */
    public function setRoleCache(?IRoleCache $cache): void
    {
        $this->userAuthorizator->setRoleCache($cache);
    }

    /**
     * Get role cache.
     */
    public function getRoleCache(): ?IRoleCache
    {
        return $this->userAuthorizator->getRoleCache();
    }

    /**
     * Set right cache.
     */
    public function setRightCache(?IRightCache $cache): void
    {
        $this->roleAuthorizator->setRightCache($cache);
    }

    /**
     * Get right cache.
     */
    public function getRightCache(): ?IRightCache
    {
        return $this->roleAuthorizator->getRightCache();
    }

    /**
     * Check that user is allowed for operation.
     * @param int $right IRight::R_* (R_LIST, R_SHOW, R_EDIT, R_ADD, R_DELETE)
     * @param IResource|int|null $resource IResource or resource type (int)
     * @param IUser $user
     * @param IResource|null $parentResource context resource, only for R_ADD or R_LIST
     */
    public function isAllowed(
        int $right,
        $resource,
        IUser $user,
        ?IResource $parentResource = null
    ): bool
    {
        if ($right < 1 || $right >= IRight::R_EOE) {
            throw new \InvalidArgumentException('Unknown value of $right param.');
        }

        if (!(is_int($resource) || is_object($resource) || is_null($resource))) {
            throw new \InvalidArgumentException('$resource has to be object or int or null.');
        }

        if ($this->resultCache) {
            $ret = $this->resultCache->load($right, $resource, $user, $parentResource);
            if ($ret !== null) {
                return $ret;
            }
        }

        $ret = $this->userAuthorizator->isAllowed($right, $resource, $user, $parentResource);

        if ($this->resultCache) {
            $this->resultCache->save($ret, $right, $resource, $user, $parentResource);
        }

        return $ret;
    }
}
