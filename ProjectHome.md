# Curly Inversion of Control #
## A simple IoC-Container for PHP ##
Curly-IoC is a simple [Inversion of Control container](http://martinfowler.com/articles/injection.html) for PHP. It implements the Dependency Injection pattern as a constructor and as a setter injection. It's created to be quite small, have a small footprint and very easy to understand.

The input for the main container instance is an object definition. Currently just a xml file or string is possible, but more is in the queue. In this xml file you can configure your instances and the container is doing the dirty instanciation stuff and wires the objects together.

_Here is an example for a xml configuration file:_

```
<?xml version="1.0" encoding="UTF-8" ?>
<configuration>
	<context name="app">
		<object id="db" class="PDO" allocation-policy="single">
			<ctr>
				<param>mysql:host=localhost;dbname=test</param>
				<param>username</param>
				<param>password</param>
			</ctr>
		</object>
	</context>
</configuration>
```

This file will create a context with name `app`. _Contexts are used to put some objects with the same concern together._ Inside the `app` context a [PDO](http://php.net/pdo) object is created with the id `db`. The constructor of that class requires the datasource name and the authentication data, so this stuff is noted inside the `ctr` tag in the xml file. `ctr` stands for constructor arguments. It takes an arbitrary number of `param` tags. In this case the connection is opened to a mysql database test at our local host and the user `username` with password `password` is used for authentication.

_You can use this object with the following code snippet:_

```
$container=new CI_Container();
$ctx=$container
	->parseFile('config.xml') // Specify here the appropriate filename
	->getContext('app');
$database=$ctx->getObject('db'); // Here we retrieve the created PDO instance

// Do some database stuff:
foreach($database->query('select * from myTable') as $row) {
	// ...
}
```

**You can find an easy example with Zend Framework integration [in the sourcecode](http://code.google.com/p/curly-ioc/source/browse/trunk/app/).**

### Support ###
If you have any problems, critics, suggestions, ideas, bugs or whatever on Curly-IoC please contact me via...

...mail at martin@curlybracket.de

...twitter at http://twitter.com/MKuckert