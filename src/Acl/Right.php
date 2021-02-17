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

/**
 * Static definition of right - can be useful for static ACL.
 */
class Right implements IRight
{
    /** @var ?bool */
    private $list;

    /** @var ?bool */
    private $show;

    /** @var ?bool */
    private $add;

    /** @var ?bool */
    private $delete;

    /** @var ?bool */
    private $edit;

    public function __construct(?bool $list = null, ?bool $show = null, ?bool $edit = null, ?bool $add = null, ?bool $delete = null)
    {
        $this->list = $list;
        $this->show = $show;
        $this->edit = $edit;
        $this->add = $add;
        $this->delete = $delete;
    }

    /**
     * Enable all.
     */
    public function enableAll(): self
    {
        $this->list = true;
        $this->show = true;
        $this->edit = true;
        $this->add = true;
        $this->delete = true;
        return $this;
    }

    /**
     * Disable all.
     */
    public function disableAll(): self
    {
        $this->list = false;
        $this->show = false;
        $this->edit = false;
        $this->add = false;
        $this->delete = false;
        return $this;
    }

     /**
     * Ignore all.
     */
    public function ignoreAll(): self
    {
        $this->list = null;
        $this->show = null;
        $this->edit = null;
        $this->add = null;
        $this->delete = null;
        return $this;
    }

    /**
     * Enable list.
     */
    public function enableList(): self
    {
        $this->list = true;
        return $this;
    }

    /**
     * Disable list.
     */
    public function disableList(): self
    {
        $this->list = false;
        return $this;
    }

     /**
     * Ignore list.
     */
    public function ignoreList(): self
    {
        $this->list = null;
        return $this;
    }

    /**
     * Enable show.
     */
    public function enableShow(): self
    {
        $this->show = true;
        return $this;
    }

    /**
     * Disable show.
     */
    public function disableShow(): self
    {
        $this->show = false;
        return $this;
    }

    /**
     * Ignore show.
     */
    public function ignoreShow(): self
    {
        $this->show = null;
        return $this;
    }

    /**
     * Enable edit.
     */
    public function enableEdit(): self
    {
        $this->edit = true;
        return $this;
    }

    /**
     * Disable edit.
     */
    public function disableEdit(): self
    {
        $this->edit = false;
        return $this;
    }

    /**
     * Ignore edit.
     */
    public function ignoreEdit(): self
    {
        $this->edit = null;
        return $this;
    }

    /**
     * Enable add.
     */
    public function enableAdd(): self
    {
        $this->add = true;
        return $this;
    }

    /**
     * Disable add.
     */
    public function disableAdd(): self
    {
        $this->add = false;
        return $this;
    }

    /**
     * Ignore add.
     */
    public function ignoreAdd(): self
    {
        $this->add = null;
        return $this;
    }

    /**
     * Enable delete.
     */
    public function enableDelete(): self
    {
        $this->delete = true;
        return $this;
    }

    /**
     * Disable delete.
     */
    public function disableDelete(): self
    {
        $this->delete = false;
        return $this;
    }

    /**
     * Ignore delete.
     */
    public function ignoreDelete(): self
    {
        $this->delete = null;
        return $this;
    }

    /**
     * Get right list.
     */
    public function getRList(): ?bool
    {
        return $this->list;
    }

    /**
     * Get right show.
     */
    public function getRShow(): ?bool
    {
        return $this->show;
    }

    /**
     * Get right add.
     */
    public function getRAdd(): ?bool
    {
        return $this->add;
    }

    /**
     * Get right delete.
     */
    public function getRDelete(): ?bool
    {
       return $this->delete;
    }

    /**
     * Get right edit.
     */
    public function getREdit(): ?bool
    {
        return $this->edit;
    }
}
