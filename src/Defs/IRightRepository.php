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

namespace HFechs\Rulez\Defs;

/**
 * Repository with rights.
 */
interface IRightRepository
{
    /**
     * Return rights.
     * @return iterable<IRight>
     */
    public function getRights(?IRole $role, ?int $resourceType, bool $own = false): iterable;

    /**
     * Return superior resource types.
     * @return int[]
     */
    public function getSuperiorResourceTypes(int $resourceType): iterable;
}
