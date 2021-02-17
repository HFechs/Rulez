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
 * Repository with roles.
 */
interface IRoleRepository
{
    /**
     * Return roles.
     * @return iterable<IRole>
     */
    public function getRoles(IUser $user, ?IResource $resource): iterable;
}
