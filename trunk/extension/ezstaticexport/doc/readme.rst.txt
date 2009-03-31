Configuration
=============

Généralités
-----------
Les paramètres par défaut de l'application sont définis dans le fichier ezstaticexport.ini
situé dans extension/ezstaticexport/settings.
L'idéal serait de ne pas modifier le fichier par défaut afin de permettre une migration
plus facile vers une éventuelle nouvelle version. Cela est possible en surchargeant ce fichier
globalement, dans settings/override/ezstaticexport.ini.append.php. Le fichier n'existant pas,
il sera nécessaire de le créer.

ExportSettings
--------------
Ces paramètres sont généraux à tous les exports.

HostName
''''''''
Définit le le hostname utilisé pour capturer les pages du site. Doit etre connu du serveur web
(DNS ou fichiers hosts), accessible (attention aux restrictions d'IP, mot de passe, routage...).
Utiliser lynx depuis le serveur pour vérifier l'accessibilité.

StaticStorageDir
''''''''''''''''
Dossier dans lequel seront placés les fichiers exportés. L'utilisateur du serveur web doit
avoir accès en écriture à ce dossier.

FolderPrefix
''''''''''''
Ce paramètre permet d'utiliser le module d'export statique lorsque le site est accessible via un sous dossier,
par exemple localhost/dev/voyazine (eZ est situé dans le dossier voyazine). Dans ce cas précis, il faudra
indiquer dev/voyazine/index.php/ comme FolderPrefix. Si les règles de rewrite sont correctement configurées,
le index.php ne sera pas nécessaire.
Si le site a son  propre nom de domaine avec rewrite, ce paramètre sera vide.

TargetServerList
----------------
Ce bloc de configuration permet de définir les cibles d'export.
La directive TargetServer contenue dans ce bloc est un tableau définissant les
cibles. Il est recommandé de surcharger cette directive pour chaque installation
afin de ne pas conserver les cibles fournies par défaut.
Le DefaultTargetServer _ défini plus bas _ ne fait pas partie de cette liste
et sera toujours défini.

DefaultTargetServerSettings
---------------------------
Ce bloc définit les paramètres de la cible d'exporation par défaut.

TargetServerName
''''''''''''''''
Nom de la cible. Indicatif, n'est pas utilisé au niveau systeme.

TargetServerURL
'''''''''''''''
URL de la cible. Indicatif, n'est pas utilisé au niveau systeme.

PublishingTargets
'''''''''''''''''
Tableau définissant les cibles de publication de l'export statique de la cible.
Chaque entrée de ce tableau définit une cible. Les paramètres de la cible sont
indiqués via une syntaxe de type URI.
Voir la partie concernant la publication pour le détail de cette configuration.

TargetServer-xxx
----------------
Pour chaque entrée dans [TargetServerList].TargetServer, un bloc TargetServer-
doit être créé afin de définir la cible.
Le bloc doit être nommé TargetServer-cible, cible étant le nom d'une des entrées
dans [TargetServerList].TargetServer.

Si ce bloc est configuré:

::

    [TargetServerList]
    TargetServer[]
    TargetServer[]=cible1
    TargetServer[]=cible2

deux blocs devront être créés:

::

    [TargetServer-cible1]
    [TargetServer-cible2]

Chacun de ces blocs contiendra 3 directives: TargetServerName, TargetServerName
et PublishingTargets.

TargetServerName
''''''''''''''''
Nom de la cible. Indicatif, n'est pas utilisé au niveau systeme.

TargetServerURL
'''''''''''''''
URL de la cible. Indicatif, n'est pas utilisé au niveau systeme.

PublishingTargets
'''''''''''''''''
Tableau définissant les cibles de publication de l'export statique de la cible.
Chaque entrée de ce tableau définit une cible. Les paramètres de la cible sont
indiqués via une syntaxe de type URI.
Voir la partie concernant la publication pour le détail de cette configuration.

Publication
===========

Configuration
-------------
Les cibles de publication sont définies via la directive PublishingTargets des
cibles d'export. Chaque entrée définit une cible de publication, cette cible
étant paramétrée via une syntaxe de type URI:

::

    protocol://login@host:/path/to/folder/;option1,option2,optionN

protocol
''''''''
Le seul protocole actuellement disponible est rsync.

login
'''''
Login à utiliser pour la connexion au serveur distant.

host
''''
Nom de l'hote de publication

/path/to/folder
'''''''''''''''
Chemin ou seront placés les fichiers de l'export statique.

option
''''''
Une liste d'options à transmettre au protocole de publication, séparées par des
virgules. Ces options sont définies par protocole.
Voir la documentation du driver rsync ci-dessous pour les options disponibles et
la définition de nouvelles options.

Rsync
=====
rsync est à l'heure actuelle le seul protocole de transfert supporté par l'extension.

Il est configuré via le bloc [DriverSettings-rsync].

DriverPath
----------
Définit le chemin d'accès au binaire rsync. Il est bien sur impératif que l'utilisateur
exécutant le cronjob ait accès en exécution à rsync.

DriverArgs
----------
Définit les options passées à rsync.
Chaque entrée est indexée par le nom de l'option tel qu'utilisable dans le fichier INI,
et reçoit comme valeur le paramètre ligne de commande (nommage paramètre long, avec --).

Par exemple:
::

    DriverArgs[simulation]=dry-run

Définit une option nommée simulation, qui ajoutera --dry-run à la ligne de commande.

Les options définies par défaut devraient être suffisantes, mais l'ajout de nouvelles
options ne pose aucun probleme.

Authentification
----------------
La seule manière avec les options prédéfinies d'utiliser rscync en publication est
actuellement de passer par une clé SSH publique/privée, et donc un transfert rsync
via SSH.

La publication a été testée via cette méthode et fonctionne correctement.

Rsync est également utisable pour une publication locale au serveur.
Il suffit d'utiliser une syntaxe de ce type:

::

    rsync://localhost/path/to/folder/;recursive,update,compress

Configuration workflow
======================
Un évènement de workflow personnalisé a été développé pour permettre le blocage de la
publication de contenu pendant un export statique. Afin d'etre actif, celui-ci doit
etre associé à un workflow, et ce workflow associé au trigger pre/publish (exactement
comme un évènement de type approve).

1. Setup > Workflow
2. Groupe de workflow par défaut
3. Créer un nouveau workflow
4. Ajouter l'évènement "Event / Static export token check"
5. Setup > Triggers
6. Trigger publish/before, associer le workflow créé précédemment, et confirmer

Configuration cronjob
=====================
Une configuration des cronjobs est nécessaire au bon fonctionnement de l'application.

Deux scripts cronjob sont fournis avec l'application:
 - staticexport
 - interruptexport

Cronjob scripts
---------------

staticexport
''''''''''''
Ce script gère les exports planifiés et immédiats et leur publication.

interruptexport
'''''''''''''''
Ce script est utilisé pour interrompre un éventuel export immédiat dans le cas ou
un export planifié (ayant donc une priorité supérieure) doit s'exécuter.

Configuration INI
-----------------
Ces deux scripts sont automatiquement utilisables une fois l'extension activée.
Deux groupes de workflow personnalisés sont créés: staticexport, et prestaticexport.
Toute exécution de cronjob doit automatiquement exécuter ces deux groupes, d'abord
prestaticexport, suivi de staticexport. Le premier prend soin d'interrompre un
éventuel export immédiat pour laisser la priorité à un export planifié.

Avec une syntaxe similaire à celle de ezpublish.cron, la configuration cron est
similaire à ceci:

::

    0,15,30,45 * * * * cd $EZPUBLISHROOT && $PHP runcronjobs.php prestaticexport -q 2>&1 && $PHP runcronjobs.php staticexport -q 2>&1

Cela assurera que prestaticexport est toujours exécuté avant staticexport.
Les deux scripts ne peuvent pas faire partie du meme process du au token d'execution
des crons qui empeche le chevauchement de deux crons.

Elements statiques (design)
===========================
Lors de l'ajout d'une nouvelle cible, il est nécessaire d'initialiser celle-ci.
Les éléments de design, hors templates, nécessaires au fonctionnement du site
(images, feuilles de style, javascript) doivent pour cela etre copiés depuis leur
emplacement initial vers le dossier current de la cible.

Dans le cas d'une cible nommée "cible1", le dossier suivant doit etre créé:

::

    var/staticexport/cible1/current

Dans ce dossier doivent etre copiés tous les sous éléments de l'intégralité
des design utilisés par le site, hors templates/ et override/templates.

Nous trouverons donc dans ce dossier des chemins semblables à:

::

    var/staticexport/cible1/current/design/mondesign/images/monimage.png