F-Project Test Server using RESTful
================================

This project provides a RESTful endpoint to test F-Project client framework


INSTALLATION
------------

### Clone this git repository and deploy to your server

After cloning, open command prompt /terminal and run this command under the root folder of repository:


~~~
composer update --ignore-platform-reqs
~~~


### Config your local web server

You have to config your local web server to make this site available at this endpoint URL:

~~~
http://localhost/fprj-test/
~~~

### Create the database and run migration

You must create a database named `fprjtest` under your MySQL database server
you can specify another database in the Yii2's configuration:

```php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=fprjtest',
    'username' => 'root',
    'password' => '1234',
    'charset' => 'utf8',
];
```

And now in the command prompt, run the migration to initialize the database:

~~~
yii migrate
~~~

OK, now you can run the F-Project client test suites