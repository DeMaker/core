# DeMaker/core
Automated class generation. Allows to create DTO's with PHPUnit tests. DeMaker supports PSR-4 namespaces (defined in `composer.json`).

## Installation
```
composer require demaker/core
```

## Usage
```
php bin/demaker dto <fully qualified class name> [fully qualified test class name] -i attribute1:string,attribute2:\\Carbon\\Carbon
```

Example:

```
php bin/demaker dto Foo\\Bar tests\\Foo\\Bar -i firstname:string,lastname:string,dob:\\Carbon\\Carbon
```
