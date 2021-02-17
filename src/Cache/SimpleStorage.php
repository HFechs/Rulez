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
 * Cache interface.
 */
class SimpleStorage implements IStorage
{
    /** @var array */
    private $cache;

    /**
     * Load item from cache.
     * @param string $key
     * @return mixed null if item doesn't exists
     */
    public function load(string $key)
    {
        return $this->cache[$key] ?? null;
    }

    /**
     * Remove item from cache.
     */
    public function remove(string $key): void
    {
        unset($this->cache[$key]);
    }

    /**
     * Save item to cache (update if exists).
     */
    public function save($value, string $key): void
    {
        $this->cache[$key] = $value;
    }
}