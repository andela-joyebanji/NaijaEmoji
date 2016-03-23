|Build Status| |Scrutinizer Code Quality| |Coverage Status|

NaijaEmoji
==========

`NaijaEmoji <http://naijaemoji.readthedocs.org/en/latest/>`__ is a
simple Restful API using Slim for NaijaEmoji Service.

Usage
=====

Clone this repository like so:

::

        git clone https://github.com/andela-joyebanji/NaijaEmoji.git

Change your directory to ``NaijaEmoji`` directory like so:

::

        cd NaijaEmoji

You need set your environment variables to define your database
parameters or rename ``.env.example`` file in project to ``.env`` and
change the below to your local configuration.

::

    DRIVER   = sqlite
    HOSTNAME = 127.0.0.1
    USERNAME = username
    PASSWORD = password
    DBNAME   = YourDatabase
    PORT     = port

Security
========

If you discover any security related issues, please email `Oyebanji
Jacob <oyebanji.jacob@andela.com>`__ or create an issue.

Credits
=======

`Oyebanji Jacob <https://github.com/andela-joyebanji>`__

License
=======

The MIT License (MIT)
---------------------

Copyright (c) 2016 Oyebanji Jacob oyebanji.jacob@andela.com

    Permission is hereby granted, free of charge, to any person
    obtaining a copy of this software and associated documentation files
    (the "Software"), to deal in the Software without restriction,
    including without limitation the rights to use, copy, modify, merge,
    publish, distribute, sublicense, and/or sell copies of the Software,
    and to permit persons to whom the Software is furnished to do so,
    subject to the following conditions:

    The above copyright notice and this permission notice shall be
    included in all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
    EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
    MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
    NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS
    BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN
    ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
    CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
    SOFTWARE.

.. |Build Status| image:: https://travis-ci.org/andela-joyebanji/NaijaEmoji.svg?branch=develop
   :target: https://travis-ci.org/andela-joyebanji/NaijaEmoji
.. |Scrutinizer Code Quality| image:: https://scrutinizer-ci.com/g/andela-joyebanji/NaijaEmoji/badges/quality-score.png?b=develop
   :target: https://scrutinizer-ci.com/g/andela-joyebanji/NaijaEmoji/?branch=develop
.. |Coverage Status| image:: https://coveralls.io/repos/github/andela-joyebanji/NaijaEmoji/badge.svg?branch=develop
   :target: https://coveralls.io/github/andela-joyebanji/NaijaEmoji?branch=develop
