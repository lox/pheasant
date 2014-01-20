
.. _configuring:

Configuration
=============

Autoloading
-----------

Pheasant relies on a :term:`PSR-0` class-loader, such as the one in `Composer <http://getcomposer.com>`_.

.. code-block:: php

    <?php

    // use composer's autoload mechanism
    require_once('vendor/autoload.php');


MySQL Connection
----------------

The simplest way to configure Pheasant is to use static `setup()` method:


.. code-block:: php

    <?php

    Pheasant::setup('mysql://user:pass@localhost:3306/mydb');


.. note::

  It's possible to pass in query parameters to this DSN for setting connection parameters:

  .. code-block:: php

      <?php

      Pheasant::setup('mysql://user:pass@localhost:3306/mydb?charset=utf8&strict=true');

  Supported parameters are:

  charset
     The character set to set for the connection, defaults to utf8

  timezone
     Any mysql timezone string, defaults to UTC

  strict
     Set strict mode on the connection. This is generally not a good idea, as it creates issues
     with binding.


Multiple Connections
~~~~~~~~~~~~~~~~~~~~

At present domain objects can only use the `default` connection, but you can setup and access other
connections via the connection manager:

.. code-block:: php

    <?php

    $pheasant = Pheasant::instance();

    // define the connections
    $pheasant->connections()->addConnection('mydb1', 'mysql://user:pass@localhost:3306/mydb');
    $pheasant->connections()->addConnection('mydb2', 'mysql://user:pass@localhost:3306/another');

    // look them up
    $pheasant->connection('mydb1')->execute('SELECT * FROM mytable');


Allowing Pheasant objects to use different connections is a feature that is under development.

.. glossary::

   PSR-0
      A standard for for interoperable PHP autoloaders See https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
