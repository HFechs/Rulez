<?php
/*
 * This file is part of the Rulez library (https://github.com/HFechs/Rulez)
 *
 * Copyright (c) 2021 Václav Švirga (HFechs)
 *
 * For the full copyright and license information, please view the file
 * license.txt that was distributed with this source code.
 */

/**
 * We will demonstrate simple RoleRepository for statically defined ACL.
 */
declare(strict_types=1);

namespace HFechs\Rulez\Examples\HelloWorld;

use HFechs\Rulez\Acl\Right;
use HFechs\Rulez\Acl\RightRepository;
use HFechs\Rulez\Defs\IResource;
use HFechs\Rulez\Defs\IResourceManager;
use HFechs\Rulez\Defs\IRole;
use HFechs\Rulez\Defs\IRoleRepository;
use HFechs\Rulez\Defs\IUser;
use HFechs\Rulez\Rulez;

require __DIR__ . '/../../vendor/autoload.php';

class HelloWorld
{
    public function run()
    {
        //Definition of roles:
        $roleAdmin = new RoleAdmin();
        $roleUser = new RoleUser();
        $roleGuest = new RoleGuest();

        /**************************** Definition of rights ****************************/

        $acl = new RightRepository();

        //Everybody can list and show articles:
        $acl->addRight(null, Resource::R_ARTICLE, (new Right())->enableList()->enableShow());
        //Everybody can list and show comments:
        $acl->addRight(null, Resource::R_COMMENT, (new Right())->enableList()->enableShow());
        //Admin can all:
        $acl->addRight($roleAdmin, null, (new Right())->enableAll());
        //User can create comment:
        $acl->addRight($roleUser, Resource::R_COMMENT, (new Right())->enableAdd());
        //User can edit and delete own's comment:
        $acl->addRight($roleUser, Resource::R_COMMENT, (new Right())->enableEdit()->enableDelete(), true);
        //Guest can't show and list comments
        $acl->addRight($roleGuest, Resource::R_COMMENT, (new Right())->disableShow()->disableList());

        /******************************* Use of authorizator **************************/

        //entity:
        $admin = new User($roleAdmin);
        $user = new User($roleUser);
        $guest = new User($roleGuest);
        $article = null;
        $comment1 = null;
        $comment2 = null;

        //test of authorization:
        $rulez = new Rulez($acl, new RoleRepository(), new ResourceManager());

        if ($rulez->isAllowed(Right::R_ADD, Resource::R_ARTICLE, $admin)) {
            echo("Admin can add article.\n");
            $article = new Article();
        }
        if (!$rulez->isAllowed(Right::R_EDIT, $article, $user)) {
            echo("User can't edit article.\n");
        }
        if ($rulez->isAllowed(Right::R_ADD, Resource::R_COMMENT, $admin)) {
            echo("Admin can add comment.\n");
            ($comment1 = new Comment())->setUser($admin);
        }
        if ($rulez->isAllowed(Right::R_ADD, Resource::R_COMMENT, $user)) {
            echo("User can add comment.\n");
            ($comment2 = new Comment())->setUser($user);
        }
        if (!$rulez->isAllowed(Right::R_DELETE, $comment1, $user)) {
            echo("User can't delete admin's comment.\n");
        }
        if ($rulez->isAllowed(Right::R_EDIT, $comment2, $user)) {
            echo("User can edit own's comment.\n");
        }
        if ($rulez->isAllowed(Right::R_LIST, Resource::R_ARTICLE, $user)) {
            echo("User can list articles.\n");
        }
        if ($rulez->isAllowed(Right::R_SHOW, $article, $guest)) {
            echo("Guest can show article.\n");
        }
        if (!$rulez->isAllowed(Right::R_SHOW, $comment1, $guest)) {
            echo("Guest can't show comment.\n");
        }
    }
}


/*********************************** Roles ************************************/

class RoleAdmin implements IRole {}

class RoleUser implements IRole {}

class RoleGuest implements IRole {}

class RoleRepository implements IRoleRepository
{
    public function getRoles(IUser $user, ?IResource $resource): iterable
    {
        /** @var User $user */
        return [$user->getRole()];
    }
}

/*********************************** Resources ********************************/

class ResourceManager implements IResourceManager
{
    public function hasUserSameSpaceAsResource(IUser $user, IResource $resource): bool
    {
        return true;
    }

    public function isUserOwnerOfResource(IUser $user, IResource $resource): bool
    {
        /** @var Resource $resource */
        return $resource->getUser() === $user;
    }
}

abstract class Resource implements IResource
{
    const R_ARTICLE = 1;
    const R_COMMENT = 2;
    const R_USER = 3;

    /** @var int */
    static protected $resourceType;

    public function getResourceType(): int
    {
        return static::$resourceType;
    }

    public function getUser(): ?User
    {
        return null;
    }
}

class User extends Resource implements IUser
{
    static protected $resourceType = self::R_USER;

    /** @var IRole */
    private $role;

    public function __construct(IRole $role)
    {
        $this->role = $role;
    }

    public function getRole(): IRole
    {
        return $this->role;
    }
}

class Article extends Resource
{
    static protected $resourceType = self::R_ARTICLE;
}

class Comment extends Resource
{
    static protected $resourceType = self::R_COMMENT;

    /** @var User */
    private $user;

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}

(new HelloWorld())->run();