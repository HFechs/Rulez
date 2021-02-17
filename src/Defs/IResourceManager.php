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
 * Do some operations with resources.
 */
interface IResourceManager
{
    /**
     * Has the user same space as the resource?
     */
    public function hasUserSameSpaceAsResource(IUser $user, IResource $resource): bool;

    /**
     * Is the user owner of the resource?
     */
    public function isUserOwnerOfResource(IUser $user, IResource $resource): bool;
}
