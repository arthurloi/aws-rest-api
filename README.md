# aws-rest-api

## Getting Started

PHP and Composer is expected to be installed on your system.

### Installing Composer

For instructions on how to install Composer visit [getcomposer.org](https://getcomposer.org/download/).

### Installing

After cloning this repository, change into the newly created directory and run

```bash
composer install
```
or if you have installed Composer locally in your current directory

```bash
php composer.phar install
```
This will install all dependencies needed for the project.

## Running the Application

To launch the API, execute :

```bash
php -S localhost:8080 -t public index.php
```
### '/running'

GET Request to the rest API to know if it is up.

### '/listid'

GET Request to receive the list of instances of the client.
Take as query parameters :
'region' : region of your AWS account.\
'key' : key of your AWS account.\
'secret' : secret of your AWS account.\

### '/manage'

GET Request to receive the list of instances of the client.

Take as query parameters :
'region' : region of your AWS account.\
'key' : key of your AWS account.\
'secret' : secret of your AWS account.\
'action' : 'start' or 'stop'. Depends if you want to start or stop an instance.\
'id' : the instance id which you want to start or stop.\

## Built With

  - [PHP](https://secure.php.net/)
  - [Composer](https://getcomposer.org/)
