# Introduction
Rulez is ACL library for PHP.

## Features

* The library is small and simple.
* You can have ACL defined statically (in code) or dynamically (in DB for example).
* It doesn't care about the storage/DB/ORM library. There is an interface for repositories with rules. The implementation is up to you.
* All resources are objects - compatible with ORM.
* ACL works in terms of users, their roles, resources and rights.
* User's owned resource can have special rights.
* A user can have more than one roles and a user role can depend on a  resource (you can implement a tree of resources or ACL based on labels).
* Basic rights are list, show, edit, add, delete.
* Rights can form a hierarchy - you can define a right that inherits from other rights.
* If you use dynamical rights (from DB), the library supports caching.

# Installation 

Add repository to composer.json:

    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/HFechs/Rulez.git"
        }
    ]
    
And then:

    composer require hfechs/rulez dev-main

# Documentation

## Hello world

In first example we use Rulez with statically defined rules. Full example is in examples/HelloWorld/HelloWorld.php.

For definition of rules we can use prearranged class RightRepository that is simple implementation of IRightRepository:

```php
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
```

Now we can check permissions in the code with Rulez:

```php
$rulez = new Rulez($acl, new RoleRepository(), new ResourceManager());

if ($rulez->isAllowed(Right::R_ADD, Resource::R_ARTICLE, $admin)) {
    echo("Admin can add article.\n");
    $article = new Article();
}
if (!$rulez->isAllowed(Right::R_EDIT, $article, $user)) {
    echo("User can't edit article.\n");
}
```

## More

Please be patient - I have the completion of the documentation in todo. 

Meanwhile you can explore tests/RulezTest.php.


# License
WTFPL 3.1

Copyright (c) 2021 Václav Švirga (HFechs) <svirga@gmail.com>
