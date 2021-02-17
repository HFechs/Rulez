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

use HFechs\Rulez\Defs\IRight;
use HFechs\Rulez\Defs\IRole;

/**
 * Authorizator of roles
 */
final class RolesAuthorizator
{
    const STATE_ROLE_RESOURCE_OWN = 1;
    const STATE_ROLE_RESOURCE = 2;
    const STATE_GROLE_RESOURCE_OWN = 3;
    const STATE_GROLE_RESOURCE = 4;
    const STATE_ROLE_GRESOURCE = 5;
    const STATE_GROLE_GRESOURCE = 6;
    const STATE_EOE = 7;

    /** @var RoleAuthorizator */
    private $roleAuthorizator;

    public function __construct(RoleAuthorizator $roleAuthorizator)
    {
        $this->roleAuthorizator = $roleAuthorizator;
    }

    /**
     * Is allowed by role, does advanced matching
     * @param int $right IRight::R_*
     * @param iterable<IRole> $roles
     * @param int|null $resourceType
     */
    public function isAllowed(int $right, iterable $roles, ?int $resourceType = null, bool $own = false): bool
    {
        if ($right < 1 || $right > IRight::R_EOE) {
            throw new \InvalidArgumentException("Bad type of right.");
        }

        foreach ($this->getStates() as $state) {
            switch ($state) {
                case self::STATE_ROLE_RESOURCE_OWN:
                    if ($resourceType && $own) {
                        $ret = null;
                        foreach ($roles as $role) {
                            $r = $this->roleAuthorizator->isAllowed($right, $role, $resourceType, true);
                            if ($r !== null) {
                                $ret = $ret || $r;
                            }
                        }
                        if ($ret !== null) {
                            return $ret;
                        }
                    }
                    break;

                case self::STATE_ROLE_RESOURCE:
                    if ($resourceType) {
                        $ret = null;
                        foreach ($roles as $role) {
                            $r = $this->roleAuthorizator->isAllowed($right, $role, $resourceType, false);
                            if ($r !== null) {
                                $ret = $ret || $r;
                            }
                        }
                        if ($ret !== null) {
                            return $ret;
                        }
                    }
                    break;

                case self::STATE_GROLE_RESOURCE_OWN:
                    if ($own && $resourceType) {
                        $ret = $this->roleAuthorizator->isAllowed($right, null, $resourceType, true);
                        if ($ret !== null) {
                            return $ret;
                        }
                    }
                    break;

                case self::STATE_GROLE_RESOURCE:
                    if ($resourceType) {
                        $ret = $this->roleAuthorizator->isAllowed($right, null, $resourceType, false);
                        if ($ret !== null) {
                            return $ret;
                        }
                    }
                    break;

                case self::STATE_ROLE_GRESOURCE:
                    $ret = null;
                    foreach ($roles as $role) {
                        $r = $this->roleAuthorizator->isAllowed($right, $role, null, false);
                        if ($r !== null) {
                            $ret = $ret || $r;
                        }
                    }
                    if ($ret !== null) {
                        return $ret;
                    }
                    break;

                case self::STATE_GROLE_GRESOURCE:
                    $ret = $this->roleAuthorizator->isAllowed($right, null, null, false);
                    if ($ret !== null) {
                        return $ret;
                    }
                    break;

                default:
                    throw new \RuntimeException('Unknown state!');
                    break;
            }
        }

        return false;
    }

    private function getStates(): array
    {
        $states = [];
        for ($i = 1; $i < self::STATE_EOE; $i++) {
            $states[] = $i;
        }
        return $states;
    }
}
