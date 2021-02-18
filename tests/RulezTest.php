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

namespace HFechs\Rulez\Tests;

use Tester\Assert;
use Tester\TestCase;
use HFechs\Rulez\Acl\Right;
use HFechs\Rulez\Acl\RightRepository;
use HFechs\Rulez\Defs\IResource;
use HFechs\Rulez\Defs\IResourceManager;
use HFechs\Rulez\Defs\IRole;
use HFechs\Rulez\Defs\IRoleRepository;
use HFechs\Rulez\Defs\IUser;
use HFechs\Rulez\Rulez;

require __DIR__ . '/bootstrap.php';

class RulezTest extends TestCase
{
    /** @var Rulez */
    private $rulez;

    /** @var RightRepository */
    private $rightRepository;

    /** @var ResourceManager */
    private $resourceManager;

    public function setUp()
    {
        $this->rightRepository = new RightRepository();
        $this->resourceManager = new ResourceManager();
        $this->rulez = new Rulez($this->rightRepository, new RoleRepository(), $this->resourceManager);
        $this->setRights();
    }

    protected function setRights()
    {
        $acl = $this->rightRepository;

        //We have to define callback for compare roles (default object identity we can't use in this test)
        $acl->setRolesEqualCallback(function(?Role $a, ?Role $b): bool {
            if ($a === $b) {
                return true;
            } elseif ($a === null || $b === null) {
                return false;
            } else {
                return $a->getRole() === $b->getRole();
            }
        });

        //Now we have to define rights:

        //all can list
        $acl->addRight(null, null, (new Right())->enableList());
        //nobody can list RESOURCE_B
        $acl->addRight(null, Resource::RESOURCE_B, (new Right())->disableList());
        //role restrict can anything
        $acl->addRight(new Role(Role::RESTRICT), null, (new Right())->disableAll());
        //role reader can list implicitly, but it doesn't help for listing RESOURCE_B (useless rule)
        $acl->addRight(new Role(Role::READER), null, (new Right())->enableList());
        //admin can all (except list RESOURCE_B)
        $acl->addRight(new Role(Role::ADMIN), null, (new Right())->enableAll());
        //now admin can list RESOURCE_B
        $acl->addRight(new Role(Role::ADMIN), Resource::RESOURCE_B, (new Right())->enableList());
        //everybody can show RESOURCE_A (restrict too!)
        $acl->addRight(null, Resource::RESOURCE_A, (new Right())->enableShow());
        //restrict can't show RESOURCE_A
        $acl->addRight(new Role(Role::RESTRICT), Resource::RESOURCE_A, (new Right())->disableShow());
        //editor can delete RESOURCE_A, but only own RESOURCE_A
        $acl->addRight(new Role(Role::EDITOR), Resource::RESOURCE_A, (new Right())->enableDelete(), true);
        //editor can edit RESOURCE_A
        $acl->addRight(new Role(Role::EDITOR), Resource::RESOURCE_A, (new Right())->enableEdit());
        //RESOURCE_C inherits rules from RESOURCE_A
        $acl->addSubresourceToResource(Resource::RESOURCE_C, Resource::RESOURCE_A);
        //editor can add RESOURCE_B
        $acl->addRight(new Role(Role::EDITOR), Resource::RESOURCE_B, (new Right())->enableAdd());
        //reader can show RESOURCE_D
        $acl->addRight(new Role(Role::READER), Resource::RESOURCE_D, (new Right())->enableShow());
        //editor can editor RESOURCE_D, but implicitly can't show RESOURCE_D
        $acl->addRight(new Role(Role::EDITOR), Resource::RESOURCE_D, (new Right())->enableEdit()->disableShow());
    }

    public function testIsAllowedSpaces()
    {
        $r1 = new ResourceA(Spaces::SPACE_A);
        $r2 = new ResourceA(Spaces::SPACE_B);
        $user = new User(Spaces::SPACE_A, new Role(Role::ADMIN));

        Assert::same(true, $this->rulez->isAllowed(Right::R_EDIT, $r1, $user));
        Assert::same(false, $this->rulez->isAllowed(Right::R_EDIT, $r2, $user));
    }

    public function testIsAllowedAllRights()
    {
        $user1 = new User(Spaces::SPACE_A, new Role(Role::ADMIN));
        $user2 = new User(Spaces::SPACE_A, new Role(Role::RESTRICT));
        $r1 = new ResourceA(Spaces::SPACE_A);

        Assert::same(true, $this->rulez->isAllowed(Right::R_LIST, Resource::RESOURCE_A, $user1));
        Assert::same(true, $this->rulez->isAllowed(Right::R_ADD, Resource::RESOURCE_A, $user1));
        Assert::same(true, $this->rulez->isAllowed(Right::R_SHOW, $r1, $user1));
        Assert::same(true, $this->rulez->isAllowed(Right::R_EDIT, $r1, $user1));
        Assert::same(true, $this->rulez->isAllowed(Right::R_DELETE, $r1, $user1));

        Assert::same(false, $this->rulez->isAllowed(Right::R_LIST, Resource::RESOURCE_A, $user2));
        Assert::same(false, $this->rulez->isAllowed(Right::R_ADD, Resource::RESOURCE_A, $user2));
        Assert::same(false, $this->rulez->isAllowed(Right::R_SHOW, $r1, $user2));
        Assert::same(false, $this->rulez->isAllowed(Right::R_EDIT, $r1, $user2));
        Assert::same(false, $this->rulez->isAllowed(Right::R_DELETE, $r1, $user2));
    }

    public function testIsAllowedDefaultRightAndHiearchy()
    {
        $r1 = new ResourceA(Spaces::SPACE_A);
        $r2 = new ResourceB(Spaces::SPACE_A);
        $user1 = new User(Spaces::SPACE_A, new Role(Role::READER));
        $user2 = new User(Spaces::SPACE_A, new Role(Role::ADMIN));
        $user3 = new User(Spaces::SPACE_A, new Role(Role::RESTRICT));

        Assert::same(true, $this->rulez->isAllowed(Right::R_LIST, Resource::RESOURCE_A, $user1));
        Assert::same(false, $this->rulez->isAllowed(Right::R_LIST, Resource::RESOURCE_B, $user1));
        Assert::same(true, $this->rulez->isAllowed(Right::R_LIST, Resource::RESOURCE_B, $user2));
        Assert::same(true, $this->rulez->isAllowed(Right::R_SHOW, $r1, $user1));
        Assert::same(false, $this->rulez->isAllowed(Right::R_SHOW, $r1, $user3));
    }

    public function testIsAllowedOwnRight()
    {
        $r1 = new ResourceA(Spaces::SPACE_A);
        $user = new User(Spaces::SPACE_A, new Role(Role::EDITOR));

        Assert::same(false, $this->rulez->isAllowed(Right::R_DELETE, $r1, $user, null));
        $this->resourceManager->own = true;
        Assert::same(true, $this->rulez->isAllowed(Right::R_DELETE, $r1, $user, null));
        $this->resourceManager->own = false;
    }

    public function testIsAllowedInheritedRight()
    {
        $r3 = new ResourceC(Spaces::SPACE_A);
        $user = new User(Spaces::SPACE_A, new Role(Role::EDITOR));

        Assert::same(false, $this->rulez->isAllowed(Right::R_DELETE, $r3, $user, null));
        $this->resourceManager->own = true;
        Assert::same(true, $this->rulez->isAllowed(Right::R_DELETE, $r3, $user, null));
        $this->resourceManager->own = false;
        Assert::same(true, $this->rulez->isAllowed(Right::R_EDIT, $r3, $user, null));
        Assert::same(true, $this->rulez->isAllowed(Right::R_ADD, Resource::RESOURCE_C, $user, null));
    }

    public function testIsAllowParentResource()
    {
        $r1 = new ResourceA(Spaces::SPACE_A);
        $r2 = new ResourceA(Spaces::SPACE_B);
        $user = new User(Spaces::SPACE_A, new Role(Role::EDITOR));

        Assert::same(true, $this->rulez->isAllowed(Right::R_ADD, Resource::RESOURCE_B, $user, $r1));
        Assert::same(false, $this->rulez->isAllowed(Right::R_ADD, Resource::RESOURCE_B, $user, $r2));
    }

    public function testMultipleRoles()
    {
        $user1 = new User(Spaces::SPACE_A, new Role(Role::RESTRICT));
        $user2 = new User(Spaces::SPACE_A, new Role(Role::EDITOR));
        $user3 = new User(Spaces::SPACE_A, new Role(Role::READER));
        $r4 = new ResourceD(Spaces::SPACE_A);

        Assert::same(false, $this->rulez->isAllowed(Right::R_SHOW, $r4, $user1));
        Assert::same(true, $this->rulez->isAllowed(Right::R_EDIT, $r4, $user1));
        Assert::same(false, $this->rulez->isAllowed(Right::R_SHOW, $r4, $user2));
        Assert::same(true, $this->rulez->isAllowed(Right::R_EDIT, $r4, $user2));
        Assert::same(true, $this->rulez->isAllowed(Right::R_SHOW, $r4, $user3));
        Assert::same(true, $this->rulez->isAllowed(Right::R_EDIT, $r4, $user3));
    }
}

class Spaces
{
    const SPACE_A = 1;
    const SPACE_B = 2;
}

abstract class Resource implements IResource
{
    const USER = 1;
    const RESOURCE_A = 2;
    const RESOURCE_B = 3;
    const RESOURCE_C = 4;
    const RESOURCE_D = 5;

    /** @var int */
    private $space;

    public function __construct(int $space)
    {
        $this->space = $space;
    }

    public function getSpace(): int
    {
        return $this->space;
    }

    public function getRoles(): iterable
    {
        return [];
    }
}

class User extends Resource implements IUser
{
    /** @var Role */
    private $role;

    public function __construct(int $space, Role $role)
    {
        $this->role = $role;
        parent::__construct($space);
    }

    public function getResourceType(): int
    {
        return Resource::USER;
    }

    public function getRole(): Role
    {
        return $this->role;
    }
}

class ResourceA extends Resource
{
    public $space;

    public function getResourceType(): int
    {
        return Resource::RESOURCE_A;
    }
}

class ResourceB extends Resource
{
    public $space;

    public function getResourceType(): int
    {
        return Resource::RESOURCE_B;
    }
}

class ResourceC extends Resource
{
    public $space;

    public function getResourceType(): int
    {
        return Resource::RESOURCE_C;
    }
}

class ResourceD extends Resource
{
    public $space;

    public function getResourceType(): int
    {
        return Resource::RESOURCE_D;
    }

    public function getRoles(): iterable
    {
        return [new Role(Role::EDITOR)];
    }
}

class Role implements IRole
{
    const ADMIN = 1;
    const EDITOR = 2;
    const READER = 3;
    const RESTRICT = 4;

    /** @var int */
    public $role;

    public function __construct(int $role)
    {
        $this->role = $role;
    }

    public function getRole()
    {
        return $this->role;
    }
}

class ResourceManager implements IResourceManager
{
    public $own = false;

    public function hasUserSameSpaceAsResource(IUser $user, IResource $resource): bool
    {
        /** @var User $user */
        /** @var Resource $resource */
        return $user->getSpace() === $resource->getSpace();
    }

    public function isUserOwnerOfResource(IUser $user, IResource $resource): bool
    {
        return $this->own;
    }
}

class RoleRepository implements IRoleRepository
{
    public function getRoles(IUser $user, ?IResource $resource): iterable
    {
        /** @var User $user */
        /** @var Resource $resource */
        return array_merge([$user->getRole()], $resource ? $resource->getRoles() : []);
    }
}

(new RulezTest)->run();

