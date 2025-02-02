Updates
=======

.. warning:: Bitte stets auf `GitHub <https://github.com/schulit/icc>`_ die entsprechende Changelog-Datei lesen.

.. danger:: Ein Update auf eine nächsthöhere Version (bspw. von 1.x auf 2.0) ist im laufenden Schuljahr nicht empfohlen. Kleinere Updates (1.1.x -> 1.x) sind auch im laufenden Schuljahr möglich.

Schritt 1: Quelltext aktualisieren
----------------------------------

Möglichkeit 1: Installation mit Git
###################################

Wenn das ICC aus dem Git installiert wurde, kann man mittels Git auf eine neue Version wechseln (``VERSION`` durch die entsprechende
Version ändern):

.. code-block:: shell

    $ git fetch
    $ git switch -b VERSION

Anschließend folgende Kommandos ausführen:

.. code-block:: shell

    $ composer install --no-dev --optimize-autoloader --no-scripts
    $ php bin/console bazinga:js-translation:dump assets/js/ --merge-domains
    $ yarn encore production

Möglichkeit 2: Installation ohne Git
####################################

Die Verzeichnisse ``node_modules``, ``public/build``, ``public/bundles``, ``src``, ``templates``, ``translations`` und ``vendor`` löschen:

.. code-block:: shell

    $ rm -rf node_modules/
    $ rm -rf public/build/
    $ rm -rf public/bundles/
    $ rm -rf src/
    $ rm -rf templates/
    $ rm -rf translations/
    $ rm -rf vendor/

Anschließend den neuen Quelltext von `GitHub <https://github.com/schulit/icc/releases>`_ herunterladen und aufspielen.

Schritt 2: Anwendung aktualisieren
----------------------------------

Nun die folgenden Kommandos ausführen, um die Anwendung zu aktualisieren:

.. code-block:: shell

    $ php bin/console cache:clear
    $ php bin/console doctrine:migrations:migrate --no-interaction
    $ php bin/console app:setup
    $ php bin/console shapecode:cron:scan

Die Anwendung ist nun aktualisiert.

Schritt 3: neue Rollen implementieren (falls notwendig)
-------------------------------------------------------

Bei neuen Funktionen kann es sein, dass neue `Rollen <./roles.html>`_ eingeführt werden. Diese müssen im Identity Provider
entsprechend hinterlegt werden. Siehe `Schritt 4.2 der Installationsanleitung <./install.html>`_.