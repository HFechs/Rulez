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

namespace HFechs\Rulez\Acl;

use HFechs\Rulez\Defs\IRight;
use HFechs\Rulez\Defs\IRightRepository;
use HFechs\Rulez\Defs\IRole;

/**
 * Static definitions of relation between roles and right. Can be useful for static ACL.
 */
class RightRepository implements IRightRepository
{
    const C_ROLE = 0;
    const C_RESOURCE = 1;
    const C_SUBRESOURCE = 2;
    const C_RIGHT = 3;
    const C_OWN = 4;

    /** @var array role|resource|subresource|right|own */
    protected $rights;

    /** @var ?callable(?IRole $role1, ?IRole $role2): bool */
    private $rolesEqualCallback;

    /**
     * Return rights.
     * @return iterable<IRight>
     */
    public function getRights(?IRole $role, ?int $resourceType, bool $own = false): iterable
    {
        $out = $this->search([
            self::C_ROLE => $role,
            self::C_RESOURCE => $resourceType,
            self::C_SUBRESOURCE => null,
            self::C_OWN => $own]);
        return array_map(function($right) {return $right[self::C_RIGHT];}, $out);
    }

    /**
     * Return superior resource types.
     * @return int[]
     */
    public function getSuperiorResourceTypes(int $resourceType): iterable
    {
        $out = $this->search([self::C_SUBRESOURCE => $resourceType]);
        return array_map(function($right) {return $right[self::C_RESOURCE];}, $out);
    }

    /**
     * Add right.
     */
    public function addRight(?IRole $role, ?int $resourceType, IRight $right, bool $onlyOwn = false): self
    {
        $this->rights[] = [
            self::C_ROLE => $role,
            self::C_RESOURCE => $resourceType,
            self::C_SUBRESOURCE => null,
            self::C_RIGHT => $right,
            self::C_OWN => $onlyOwn
        ];
        return $this;
    }

    /**
     * Add relation between resource and subresource.
     */
    public function addSubresourceToResource(int $subresourceType, int $resourceType): self
    {
        $this->rights[] = [
            self::C_ROLE => null,
            self::C_RESOURCE => $resourceType,
            self::C_SUBRESOURCE => $subresourceType,
            self::C_RIGHT => null,
            self::C_OWN => false
        ];
        return $this;
    }

    /**
     * @param callable(?IRole $role1, ?IRole $role2): bool $callable
     */
    public function setRolesEqualCallback(callable $callable): void
    {
        $this->rolesEqualCallback = $callable;
    }

    protected function search(array $search): array
    {
        $output = [];
        foreach ($this->rights as $right) {
            $found = true;
            foreach ($search as $k => $v) {
                if (($k === self::C_ROLE && !$this->isRolesEqual($right[$k], $v)) ||
                    ($k !== self::C_ROLE && $right[$k] !== $v)) {
                    $found = false;
                    break;
                }
            }
            if ($found) {
                $output[] = $right;
            }
        }
        return $output;
    }

    protected function isRolesEqual(?IRole $role1, ?IRole $role2): bool
    {
        if ($this->rolesEqualCallback) {
            $callback = $this->rolesEqualCallback;
            return $callback($role1, $role2);
        }
        return $role1 === $role2;
    }
}
