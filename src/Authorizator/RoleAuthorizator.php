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

use HFechs\Rulez\Cache\IRightCache;
use HFechs\Rulez\Cache\IRoleCache;
use HFechs\Rulez\Defs\IRightRepository;
use HFechs\Rulez\Defs\IRole;
use HFechs\Rulez\Defs\IRight;

/**
 * Authorizator of role.
 */
final class RoleAuthorizator
{
    /** @var IRightRepository */
    private $rightRepository;

    /** @var ?IRightCache */
    private $rightCache;

    public function __construct(IRightRepository $rightRepository)
    {
        $this->rightRepository = $rightRepository;
    }

    /**
     * Is allowed by role, doesn't do advanced matching.
     * @param int $right IRight::R_*
     * @param IRole|null $role
     * @param int|null $resourceType
     * @param bool $own
     */
    public function isAllowed(int $right, ?IRole $role, ?int $resourceType = null, bool $own = false): ?bool
    {
        if ($right < 1 || $right > IRight::R_EOE) {
            throw new \InvalidArgumentException("Bad type of right.");
        }

        if (in_array($right, [IRight::R_LIST, IRight::R_ADD]) && $own) {
            throw new \InvalidArgumentException("Check right R_LIST or R_ADD with flag own = true doesn't make sense.");
        }

        $rights = null;
        if ($this->rightCache) {
            $rights = $this->rightCache->loadRights($role, $resourceType, $own);
        }
        if (!$rights) {
            $rights = $this->rightRepository->getRights($role, $resourceType, $own);
            if ($this->rightCache) {
                $this->rightCache->saveRights($rights, $role, $resourceType, $own);
            }
        }

        //direct right
        $ret = null;
        foreach ($rights as $r) {
            $rv = null;
            switch ($right) {
                case IRight::R_SHOW:
                    $rv = $r->getRShow();
                    break;

                case IRight::R_LIST:
                    $rv = $r->getRList();
                    break;

                case IRight::R_EDIT:
                    $rv = $r->getREdit();
                    break;

                case IRight::R_ADD:
                    $rv = $r->getRAdd();
                    break;

                case IRight::R_DELETE:
                    $rv = $r->getRDelete();
                    break;
            }
            if ($rv !== null) {
                $ret = $ret || $rv;
            }
        }
        if ($ret !== null) {
            return $ret;
        }

        //right from parent's
        if ($resourceType) {
            $resourceTypes = null;
            if ($this->rightCache) {
                $resourceTypes = $this->rightCache->loadSuperiorResourceTypes($resourceType);
            }
            if (!$resourceTypes) {
                $resourceTypes = $this->rightRepository->getSuperiorResourceTypes($resourceType);
                if ($this->rightCache) {
                    $this->rightCache->saveSuperiorResourceTypes($resourceTypes, $resourceType);
                }
            }

            foreach ($resourceTypes as $rt) {
                $right_ = $right;

                //We have to change R_LIST to R_SHOW in inheritance from parent to child.
                $right_ = $right_ === IRight::R_LIST ? IRight::R_SHOW : $right_;

                //We have to change R_ADD to R_EDIT in inheritance from parent to child.
                $right_ = $right_ === IRight::R_ADD ? IRight::R_EDIT : $right_;

                $r = $this->isAllowed($right_, $role, $rt, $own);
                if ($r !== null) {
                    $ret = $ret || $r;
                }
            }
            if ($ret !== null) {
                return $ret;
            }
        }

        return null;
    }


    /**
     * Set role cache.
     */
    public function setRightCache(?IRightCache $cache): void
    {
        $this->rightCache = $cache;
    }

    /**
     * Get role cache.
     */
    public function getRightCache(): ?IRightCache
    {
        return $this->rightCache;
    }
}
