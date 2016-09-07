# RepositoryServiceBundle

## What ?

A symfony bundle that eases creation of doctrine ORM repositories as services.


It will create a service for each registered entity in the default entity manager.

If you provide a `repository` tag for a service, it will automatically create an alias and configure doctrine to make it the custom repository class of the associated entity (specified by the `for` attribute).

## How ?

### install

    composer require docteurklein/repository-service-bundle

### register the bundle

``` php

    public function registerBundles()
    {
        $bundles = [
            new \DocteurKlein\RepositoryServiceBundle,
            // …
        ];

        return $bundles;
    }
```

## Examples

> Note: The following examples use JmsDiExtraBundle to simplify code.

Given an entity:

```php
namespace Model;

/** @ORM\Entity */
class Product
{
    /** @ORM\Id */
    private $id;
}
```

And the following service:

```php
namespace Repository;

/**
 * @Service("products")
 * @Tag("repository", attributes={"for"="Model\Product"})
 */
final class Products extends EntityRepository
{
}
```
Then the DIC contains a factory service named `repo.model_product` for the repository (using `ManagerRegistry::getRepository()`).

It also contains an alias named `products` pointing to the `repo.model_product` service.

The custom repository class is automatically configured to point to `Repository\\Products`.

