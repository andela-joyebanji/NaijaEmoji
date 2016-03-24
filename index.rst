|Build Status| |Scrutinizer Code Quality| |Coverage Status|

NaijaEmoji
~~~~~~~~~~

`NaijaEmoji`_ is a simple Restful API using Slim for NaijaEmoji Service.

Usage
-----

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

All examples are shown in POSTMAN.

Registration
------------

To manage emojis, you’d need to register as a user. The
``/auth/register`` route handles user registration.

You can register a user using ``POSTMAN`` like so:

.. figure:: screenshots/user_registration.png
   :alt: User Registration

   User Registration

Supply your preferred ``username`` and ``password``.

Login
-----

To make use of routes that requires token authentication, you need to
get a token. The ``/auth/login`` route handles token generation for
users. You can get token like so:

.. figure:: screenshots/user_login.png
   :alt: User Login

   User Login

Supply your registered ``username`` and ``password``. You can now use
the returned token to make other requests to restricted routes.

Get all Emojis
--------------

To get all emojis, you send a ``GET`` request to ``/emojis`` route like
so:

.. figure:: screenshots/get_all_emojis.png
   :alt: Get All Emojis

   Get All Emojis

Get an Emoji
------------

To get an emoji, you send a ``GET`` request to ``/emoji/{id of emoji}``
route like so:

.. figure:: screenshots/get_emoji.png
   :alt: Get Emoji

   Get Emoji

Create Emoji
------------

To create an emoji, you send a ``POST`` request, with your
authentication token, to ``/emojis`` route with emoji’s information like
so:

.. figure:: screenshots/create_emoji.png
   :alt: Create Emoji

   Create Emoji

Delete Emoji
------------

To delete an emoji, you send a ``DELETE`` request, with your
authentication token, to ``/emojis/{id of emoji}`` route like so:

.. figure:: screenshots/delete_emoji.png
   :alt: Delete Emoji

   Delete Emoji

``Note: You can only delete an Emoji you created personally.``

Update Emoji
------------

To update an emoji, you send a ``PUT`` or ``PATCH`` request, with your
authentication token, to ``/emojis/{id of emoji}`` route with the
information you what to update like so:

.. figure:: screenshots/update_emoji.png
   :alt: Update Emoji

   Update Emoji

``Note: You can only update an Emoji you created personally.``

Security
--------

If you

.. _NaijaEmoji: http://naijaemoji.readthedocs.org/en/latest/

.. |Build Status| image:: https://travis-ci.org/andela-joyebanji/NaijaEmoji.svg?branch=develop
   :target: https://travis-ci.org/andela-joyebanji/NaijaEmoji
.. |Scrutinizer Code Quality| image:: https://scrutinizer-ci.com/g/andela-joyebanji/NaijaEmoji/badges/quality-score.png?b=develop
   :target: https://scrutinizer-ci.com/g/andela-joyebanji/NaijaEmoji/?branch=develop
.. |Coverage Status| image:: https://coveralls.io/repos/github/andela-joyebanji/NaijaEmoji/badge.svg?branch=develop
   :target: https://coveralls.io/github/andela-joyebanji/NaijaEmoji?branch=develop