estrarre i file e metterli in una cartella chiamata plugin-name
spostare la cartella dentro wp-contents/plugins


API REST:
Creare una application password per l'utente in fondo alla pagina di impostazioni utente nella dashboard admin

Usare l'endpoint POST /wp-json/logger/token
con i paramentri: username, password, app_pass
dove app_pass è la password generata sopra

Viene restituito un token da aggiungere agli header per l'endpoint /wp-json/logger/logs

Da aggiungere all'header: Authorization "Basic {token}"


CODICE:

File editati del boilerplate:

admin/class-plugin-name-admin.php
public/class-plugin-name-public.php
includes/class-plugin-name-activator.php
includes/class-plugin-name-deactivator.php

Tempo di coding: 7:35 ore



Lorenzo Bernini
