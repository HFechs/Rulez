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
use HFechs\Rulez\Defs\IUser;
use HFechs\Rulez\Defs\IRight;

/**
 * Storage interface.
 */
interface IStorage
{
    /**
     * Load item from cache.
     * @param string $key
     * @return mixed null if item doesn't exist
     */
    public function load(string $key);

    /**
     * Remove item from cache.
     */
    public function remove(string $key): void;

    /**
     * Save item to cache (update if exists).
     */
    public function save($value, string $key): void;
}