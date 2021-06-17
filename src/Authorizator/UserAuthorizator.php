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

namespace HFechs\Rulez\Authorizator;

use HFechs\Rulez\Cache\IRoleCache;
use HFechs\Rulez\Defs\IResource;
use HFechs\Rulez\Defs\IResourceManager;
use HFechs\Rulez\Defs\IRoleRepository;
use HFechs\Rulez\Defs\IUser;
use HFechs\Rulez\Defs\IRight;

/**
 * Authorizator of user.
 */
final class UserAuthorizator
{
    /** @var RolesAuthorizator */
    private $rolesAuthorizator;

    /** @var IRoleRepository */
    private $roleRepository;

    /** @var IResourceManager */
    private $resourceManager;

    /** @var ?IRoleCache */
    private $roleCache;

    public function __construct(
        RolesAuthorizator $rolesAuthorizator,
        IRoleRepository $roleRepository,
        IResourceManager $resourceManager
    )
    {
        $this->rolesAuthorizator = $rolesAuthorizator;
        $this->roleRepository = $roleRepository;
        $this->resourceManager = $resourceManager;
    }

    /**
     * Check that user is allowed for operation.
     * @param int $right IRight::R_*
     * @param IResource|int|null $resource IResource or resource type
     * @param IUser $user ,
     * @param IResource|null $parentResource
     */
    public function isAllowed(
        int $right,
        $resource,
        IUser $user,
        ?IResource $parentResource = null
    ): bool {
        if ($right < 1 || $right > IRight::R_EOE) {
            throw new \InvalidArgumentException("Bad type of right.");
        }

        if ($resource instanceof IResource) {
            if (in_array($right, [IRight::R_LIST, IRight::R_ADD])) {
                throw new \InvalidArgumentException("Resource can't be object for ADD or LIST right.");
            }

            //User and resource doesn't have same company
            if (!$this->resourceManager->hasUserSameSpaceAsResource($user, $resource)) {
                return false;
            }
        }

        if ($parentResource) {
            //User and parent entity doesn't have same company
            if (!$this->resourceManager->hasUserSameSpaceAsResource($user, $parentResource)) {
                return false;
            }
        }

        $roles = null;
        $resource_ = $resource instanceof IResource ? $resource : $parentResource;
        if ($this->roleCache) {
            $roles = $this->roleCache->load($user, $resource_);
        }
        if (!$roles) {
            $roles = $this->roleRepository->getRoles($user, $resource_);
            if ($this->roleCache) {
                $this->roleCache->save($roles, $user, $resource_);
            }
        }

        //We have to convert resource to int and check that user owns resource
        $own = false;
        if ($resource instanceof IResource) {
            $own = $this->resourceManager->isUserOwnerOfResource($user, $resource);
            $resource = $resource->getResourceType();
        }

        return $this->rolesAuthorizator->isAllowed($right, $roles, $resource, $own);
    }

    /**
     * Set role cache.
     */
    public function setRoleCache(?IRoleCache $cache): void
    {
        $this->roleCache = $cache;
    }

    /**
     * Get role cache.
     */
    public function getRoleCache(): ?IRoleCache
    {
        return $this->roleCache;
    }
}
