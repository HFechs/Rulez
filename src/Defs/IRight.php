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
 * Right entity.
 */
interface IRight
{
    const R_SHOW = 1;
    const R_LIST = 2;
    const R_ADD = 3;
    const R_EDIT = 4;
    const R_DELETE = 5;
    const R_EOE = 6;

    /** Get right list. */
    public function getRList(): ?bool;

    /** Get right show. */
    public function getRShow(): ?bool;

    /** Get right add. */
    public function getRAdd(): ?bool;

    /** Get right delete. */
    public function getRDelete(): ?bool;

    /** Get right edit. */
    public function getREdit(): ?bool;
}
