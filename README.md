
# Namek Bank Pro API by Presley

**Projet API sur symfony 4:**

Namek Bank Pro is a bank for Enterprise. A ​Master ​is linked to ​only one​ company. A ​company ​have ​multiple credit cards​.

## Getting Started

### Installing

 use : 
 
 ``docker-compose up -d``
 ``docker-compose exec web bash``
 ``composer install``
 ``php bin/console d:s:u -force``
 ```php bin/console hautelook:fixtures:load --purge-with-truncate```


### Commands
create an admin while using ``php bin/console app:create-admin EMAIL FIRSTNAME LASTNAME``

count the number of creditcards with the following command : ``php bin/console app:user-count-creditcards``

### Built With
* [Symfony 4](https://symfony.com/4) - The Web framework used
* [PHPUnit](https://phpunit.de/) - The PHP Testing Framework
* [Alice Bundle](https://github.com/nelmio/alice) - A bundle to create fake data
* [Faker](https://github.com/fzaninotto/Faker) - PHP library to generate fake data
* [PhpMyAdmin](https://www.phpmyadmin.net/) - The software for the Database
* [Postman](https://www.getpostman.com/) - API Development Environment

## Author
**Presley Lupon** 