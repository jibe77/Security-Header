# Security-Header

Ce document a pour but de recommander l'utilisation d'en-têtes en vue d'améliorer la sécurité d'une application Web. Celles-ci vont indiquer au navigateur Web ce que l'utilisateur a le droit de faire, ou pas. 

L'intérêt est de bloquer les utilisations abusives que pourraient exploiter des attaquants notamment via :
- l'injection de code JavaScript exécuté par d'autres utilisateurs en vue de fuiter des données (attaques XSS)
- l'injection de données à l'insue d'utilisateurs (attaque ClickJacking et CSRF)
- la récupération de données en clair sur le réseau (également appelé Sniffing)
- ne pas garder de données confidentielles dans le cache du navigateur

Dans un premier temps nous présenterons :
- l'utilisation de l'en-tête Content-Security-Policy afin de contrôler les interactions de l'applications avec les ressources externes
- Strict-Transport-Security afin de forcer l'utilisation de HTTPS
- Configuration de l'accès au cookie
- Gestion de la session
- Gestion du cache

## Content-Security-Policy

Une Content Security Policy (CSP) ou stratégie de sécurité du contenu permet d'améliorer la sécurité des sites web en permettant de détecter et réduire certains types d'attaques, dont les attaques XSS (en-US) (Cross Site Scripting), ClickJacking, l'interception de données non cryptées et les injections de contenu. Ces attaques peuvent être utilisées dans divers buts, comme le vol de données, le défacement de site ou la diffusion de malware.

La définition d'un règle CSP se définit avec cette syntaxe : 

    Content-Security-Policy: règle

L'idéal est de configurer le serveur Web (tel qu'Apache2 ou NGinx) afin d'ajouter automatiquement les headers.

Il est possible d'utiliser la balise meta dans une page HTML mais cette utilisation n'est pas conseillée et ne fonctionne pas avec toutes les fonctionnalités proposées :

    <meta http-equiv="Content-Security-Policy" content="règle">

Il existe un nombre important de fonctionnalités qu'il serait fastidieux de décrire ici, mais il y a cependant certaines règles simples à mettre en place afin de couvrir la plupart des vulnérabilité.

Avec cette première règle, tous les contenus auxquels accède la page sont bloqués, ainsi que le code Javascript inline (code imbriqué dans du HTML):

    Content-Security-Policy: default-src 'self';

Il est également conseillé d'indiquer au navigateur que le site ne peut pas être inclu dans une frame, afin de limiter les vulnérabilités de type ClickJacking :

    Content-Security-Policy: default-src 'self' 'frame-ancestors 'none';';

Au cas où il est nécessaire d'accéder à un domaine externe, pour charger une librairie Javascript par exemple, il est possible de définir ce type d'exception :

    Content-Security-Policy: default-src 'self' *.source-sure.example.net

Au cas où il est nécessaire de permettre l'utilisation de code Javascript inline, il est possible de définir une exception :

    Content-Security-Policy: default-src 'self' 'unsafe-inline';

Sachez cependant que cette pratique n'est pas conseillée car cela augmente l'exploitabilité d'une injection de code Javascript (faille XSS).

D'autres fonctionnalités sont décrites et démontrées sur cette page https://github.com/jibe77/Security-Headers-CSP.

Documentation :

 - activation des headers sur un serveur Apache2 :
    - https://blog.dareboost.com/en/2016/08/how-to-implement-content-security-policy/ 
 - documentation sur les CSP sur le site de la fondation Mozilla :
    - https://developer.mozilla.org/fr/docs/Web/HTTP/CSP
 - spécification officielle du W3C:
    - niveau 1 (2015) https://www.w3.org/TR/CSP1/
    - niveau 2 (2016) https://www.w3.org/TR/CSP2/
    - niveau 3 (2021) https://www.w3.org/TR/CSP3/ 
 - support des fonctionnalités des CSP en fonction des navigateurs :
    - https://content-security-policy.com/

## Strict-Transport-Security

L'en-tête de réponse HTTP Strict-Transport-Security (souvent abrégé en HSTS) informe les navigateurs que le site ne doit être accessible qu'en utilisant HTTPS et que toute tentative future d'y accéder en utilisant HTTP doit être automatiquement convertie en HTTPS.

Si un site Web accepte une connexion via HTTP et redirige vers HTTPS, les visiteurs peuvent d'abord communiquer avec la version non cryptée du site avant d'être redirigés, si, par exemple, le visiteur tape http://www.foo.com/ ou même juste foo.com. Cela crée une opportunité pour une attaque de l'homme du milieu.

Strict Transport Security résout ce problème ; tant que vous avez accédé à votre site Web une fois en utilisant HTTPS et que le site Web utilise Strict Transport Security, votre navigateur saura utiliser automatiquement uniquement HTTPS.

La syntaxe est la suivante (la durée d'expiration est exprimée en secondes) : 

    Strict-Transport-Security: max-age=<expire-time>
    Strict-Transport-Security: max-age=<expire-time>; includeSubDomains
    Strict-Transport-Security: max-age=<expire-time>; preload;

La durée d'expiration généralement conseillée est de 2 ans, donc la règle suivante doit être spécifié dans la réponse des appels HTTP :

     Strict-Transport-Security: max-age=63072000; includeSubDomains; preload;
     

Plus d'information sur le site de Mozilla : https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Strict-Transport-Security

L'article suivant explique d'avantage le fonctionnement du header STS : https://www.acunetix.com/blog/articles/what-is-hsts-why-use-it/

## Protection des cookies

Vous pouvez vous assurer que les cookies sont envoyés en toute sécurité et ne sont pas accessibles par des parties ou des scripts non intentionnels de l'une des deux manières suivantes : avec l'attribut Secure et l'attribut HttpOnly.

Un cookie avec l'attribut Secure n'est envoyé au serveur qu'avec une requête cryptée via le protocole HTTPS. Il n'est jamais envoyé avec un HTTP non sécurisé (sauf sur localhost), ce qui signifie que les attaquants de type "man-in-the-middle" ne peuvent pas y accéder facilement.

Un cookie avec l'attribut HttpOnly est inaccessible à l'API JavaScript Document.cookie ; Cette précaution permet d'atténuer les attaques de script intersite (XSS).

  Set-Cookie: id=a3fWa; Expires=Thu, 21 Oct 2021 07:28:00 GMT; Secure; HttpOnly

Lien HTTP de création du cookie sans secure : http://192.168.3.6/header/lab2-cookies/02_create_cookie_without_secure.html
Lien pour l'affichage du contenu du cookie en HTTP : http://192.168.3.6/header/lab2-cookies/01_display_cookie_with_js.html
Lien pour l'affichage du contenu du cookie en HTTPS : https://192.168.3.6/header/lab2-cookies/01_display_cookie_with_js.html

On remarque que le cookie est lisible dans les deux cas.

Lien HTTP de création du cookie avec secure : http://192.168.3.6/header/lab2-cookies/03_create_cookie_with_secure.html
Lien pour l'affichage du contenu du cookie secure en HTTP : http://192.168.3.6/header/lab2-cookies/01_display_cookie_with_js.html
Lien pour l'affichage du contenu du cookie secure en HTTPS : https://192.168.3.6/header/lab2-cookies/01_display_cookie_with_js.html

On remarque que la création du cookie avec l'attribut Secure n'est pas possible depuis HTTP.

Lien HTTP de création du cookie avec secure : https://192.168.3.6/header/lab2-cookies/03_create_cookie_with_secure.html
Lien pour l'affichage du contenu du cookie secure en HTTP : http://192.168.3.6/header/lab2-cookies/01_display_cookie_with_js.html
Lien pour l'affichage du contenu du cookie secure en HTTPS : https://192.168.3.6/header/lab2-cookies/01_display_cookie_with_js.html

On remarque que le cookie est seulement lisible depuis HTTPS.

Lien HTTP de création du cookie avec secure en localhost : http://localhost/header/lab2-cookies/02_create_cookie_with_secure.html
Lien pour l'affichage du contenu du cookie secure en HTTP en localhost : http://localhost/header/lab2-cookies/01_display_cookie_with_js.html
Lien pour l'affichage du contenu du cookie secure en HTTPS en localhost: https://localhost/header/lab2-cookies/01_display_cookie_with_js.html

On remarque que l'attribut Secure n'est pas pris en compte en localhost (idem pour 127.0.0.1). Le cookie est créé et lu en HTTP et HTTPS.

Dans ce deuxième exemple, l'application créé le même cookie mais avec l'attribut HttpOnly.

Lien de création du cookie sans l'option HttpOnly : http://localhost/header/lab2-cookies/04_create_cookie_without_httponly.html
Lien pour accéder au contenu du cookie : http://localhost/header/lab2-cookies/01_display_cookie_with_js.html

Lien de création du cookie avec l'option HttpOnly : http://localhost/header/lab2-cookies/05_create_cookie_with_httponly.html
Lien pour accéder au contenu du cookie : http://localhost/header/lab2-cookies/01_display_cookie_with_js.html

On remarque que cela ne fonctionne pas car la création du cookie est fait via Javascript, et il est interdit de manipuler (et donc créer) un cookie HttpOnly via Javascript.

Les attributs Domain et Path définissent la partie d'un cookie, afin que le navigateur sache sur quelles URL le cookie peut être envoyé.

L'attribut Domain spécifie quels hôtes peuvent recevoir un cookie. S'il n'est pas spécifié, l'attribut utilise par défaut le même hôte qui a défini le cookie, à l'exclusion des sous-domaines. Si Domain est spécifié, les sous-domaines sont toujours inclus. Par conséquent, spécifier Domain est moins restrictif que de l'omettre.

Lien pour créer un cookie avec le domaine 127.0.0.1 alors que la page est consultée depuis localhost : http://localhost/header/lab2-cookies/06_create_cookie_with_domain.html

On remarque que le cookie n'est pas accepté : 

Cookie “username” has been rejected for invalid domain.

Lien pour créer le cookie depuis 127.0.0.1 : 

http://127.0.0.1/header/lab2-cookies/06_create_cookie_with_domain.html

On remarque que le message suivant est affiché dans Firefox : Cookie “username” does not have a proper “SameSite” attribute value. Soon, cookies without the “SameSite” attribute or with an invalid value will be treated as “Lax”. 

Note: on remarque que le cookie est présent également depuis le niveau supérieur de la hiérarchie :

http://127.0.0.1/header/lab2-cookies/

Lien pour vérifier si le cookie est lisible depuis localhost : http://localhost/header/lab2-cookies/01_display_cookie_with_js.html
Lien pour vérifier si le cookie est lisible depuis 127.0.0.1 : http://127.0.0.1/header/lab2-cookies/01_display_cookie_with_js.html

Il est donc présent en 127.0.0.1 et pas en localhost.

L'attribut Path indique un chemin d'URL qui doit exister dans l'URL demandée afin d'envoyer l'en-tête Cookie. Le caractère %x2F ("/") est considéré comme un séparateur de répertoire et les sous-répertoires correspondent également.

Par exemple, si vous définissez Path=/docs, ces chemins de requête correspondent :

    /docs
    /docs/
    /docs/Web/
    /docs/Web/HTTP

Mais ces chemins ne sont pas pris en compte :

    /
    /docsets
    /fr/docs

Lien pour créer un cookie avec le path /header : http://127.0.0.1/header/lab2-cookies/07_create_cookie_with_domain_and_path.html
Lien pour lire le contenu du cookie : 
http://127.0.0.1/header/lab2-cookies/01_display_cookie_with_js.html
On voit que cela fonctionne.
Si on modifie manuellement le path dans le cookie, on voit en rafraichissant la page qu'on ne peut plus accéder au cookie.

L'attribut SameSite permet aux serveurs de spécifier si/quand les cookies sont envoyés avec des requêtes intersites (où Site est défini par le domaine enregistrable et le schéma : http ou https). Cela offre une certaine protection contre les attaques de falsification de requêtes intersites (CSRF). Il prend trois valeurs possibles : Strict, Lax et None.

  Set-Cookie: mykey=myvalue; SameSite=Strict

Avec Strict, le cookie n'est envoyé qu'au site d'où il provient. Lax est similaire, sauf que les cookies sont envoyés lorsque l'utilisateur navigue sur le site d'origine du cookie. Par exemple, en suivant un lien depuis un site externe. Aucun spécifie que les cookies sont envoyés à la fois sur les requêtes d'origine et intersites, mais uniquement dans des contextes sécurisés (c'est-à-dire, si SameSite=None, l'attribut Secure doit également être défini). Si aucun attribut SameSite n'est défini, le cookie est traité comme Lax.

Lien pour créer un cookie sans samesite : http://127.0.0.1/header/lab2-cookies/02_create_cookie_without_secure.html
Le but est d'accéder à cette page effectuant un traitement côté serveur depuis un site situé sur un autre domaine : http://127.0.0.1/header/lab2-cookies/12_read_cookie_from_server_side_log.php
Voir si le cookie sur 127.0.0.1 est présent dans les logs lors d'un appel depuis 127.0.0.1 : http://127.0.0.1/header/lab2-cookies/13_launch_csrf_from_post.html
Voir si le cookie sur 127.0.0.1 est présent dans les logs lors d'un appel depuis 192.168.3.6 : http://192.168.3.6/header/lab2-cookies/13_launch_csrf_from_post.html

On remarque qu'il n'y a pas de problème pour le service attaqué d'accéder au cookie. 

Dans les exemples suivants, on peut voir que l'attribut SameSite bloque l'accès au cookie lorsque la page est appelée depuis un autre domaine :

Lien pour créer un cookie avec samesite : http://127.0.0.1/header/lab2-cookies/09_create_cookie_with_samesite.html
Le but est de récupérer le contenu de cette page depuis un site situé sur un autre domaine : http://127.0.0.1/header/lab2-cookies/12_read_cookie_from_server_side_log.php
Voir si le cookie sur 127.0.0.1 est présent dans les logs lors d'un appel depuis 127.0.0.1 : http://127.0.0.1/header/lab2-cookies/13_launch_csrf_from_post.html
Voir si le cookie sur 127.0.0.1 est présent dans les logs lors d'un appel depuis 192.168.3.6 : http://192.168.3.6/header/lab2-cookies/13_launch_csrf_from_post.html

Attention, cet autre exemple ne fonctionne pas dans le cas d'un lien hypertext avec la méthode GET, dans ce cas il doit être conseillé de changer pour la méthode POST :

Lien pour créer un cookie sans samesite : http://127.0.0.1/header/lab2-cookies/02_create_cookie_without_secure.html
Lien pour lancer une attaque CSRF depuis 192.168.3.6, et voir si le cookie sur 127.0.0.1 est lisible : http://192.168.3.6/header/lab2-cookies/08_launch_csrf.html
Lien pour créer un cookie avec samesite : http://127.0.0.1/header/lab2-cookies/09_create_cookie_with_samesite.html
Lien pour lancer une attaque CSRF depuis 192.168.3.6, et voir si le cookie sur 127.0.0.1 est lisible : http://192.168.3.6/header/lab2-cookies/08_launch_csrf.html

## Blocage des appels CORS

Pour permettre les appels depuis d'autres domaines, via la fonction fetch de Javascript par exemple, le service doit indiquer dans son en-tête l'attribut 'Access-Control-Allow-Origin':

   Access-Control-Allow-Origin: *
   Access-Control-Allow-Origin: <origin>
   Access-Control-Allow-Origin: null

Dans les prochains exemples, l'appel ne fonctionne pas car le header CORS n'est pas présent donc le navigateur n'autorise pas la requête vers un autre domaine avec la méthode fetch :

Lien pour créer un cookie sans samesite : http://127.0.0.1/header/lab2-cookies/02_create_cookie_without_secure.html
Le but est de récupérer le contenu de cette page depuis un site situé sur un autre domaine : http://127.0.0.1/header/lab2-cookies/10_read_cookie_from_server_side.php
voir si le cookie sur 127.0.0.1 est lisible depuis 127.0.0.1 : http://127.0.0.1/header/lab2-cookies/11_launch_csrf_from_post.html

## Gestion de la session

Une session doit être automatiquement terminée sur le client lorsque l'utilisateur ferme le navigateur en créant des cookies sans date d'expiration.

Un cookie sans date d'expiration spécifiée expirera à la fermeture du navigateur.

Voici un exemple :

  Cookie: user_id=5; remember_me=true

Création d'un cookie avec date d'expiration : http://127.0.0.1/header/lab2-cookies/14_create_cookie_with_expiration.html
On ouvre cette page, on remarque que le cookie est accessible : http://127.0.0.1/header/lab2-cookies/01_display_cookie_with_js.html
Après avoir fermé son navigateur, on ouvre la même page et le cookie est encore disponible : http://127.0.0.1/header/lab2-cookies/01_display_cookie_with_js.html

Création d'un cookie sans date d'expiration : http://127.0.0.1/header/lab2-cookies/15_create_cookie_without_expiration.html
On ouvre cette page, on remarque que le cookie est accessible : http://127.0.0.1/header/lab2-cookies/01_display_cookie_with_js.html
Après avoir fermé son navigateur, on ouvre la même page et le cookie n'est plus disponible : http://127.0.0.1/header/lab2-cookies/01_display_cookie_with_js.html

On remarque que le cookie n'est plus disponible suite au redémarrage du navigateur.

## Gestion du cache

Afin de ne pas stocker le contenu dans le cache du navigateur, il est conseillé d'utiliser les en-têtes suivants : 

 Cache-Control: no-store

Pour les fichiers de l'application qui ne seront pas modifiés, vous pouvez généralement ajouter une mise en cache pour une semaine (soit 604800 secondes) :

 Cache-Control: public, max-age=604800, immutable 

Lorsque l'utilisateur se déconnecte, la suppression du cache, des cookies et du storage est fait via une réponse HTTP contenant cet en-tête :

 Clear-Site-Data: "cache", "cookies", "storage"

L'idéal est de spécifier cet en-tête à la fermeture de la session.

Dans ce lien, on créé un cookie : http://127.0.0.1/header/lab2-cookies/02_create_cookie_without_secure.html
Dans ce lien, on affiche le contenu du cookie : http://127.0.0.1/header/lab2-cookies/01_display_cookie_with_js.html
Dans ce lien, on supprime le contenu gardé par le navigateur : http://127.0.0.1/header/lab2-cookies/16_clear_site_storage.html
Dans ce lien, on affiche le contenu du cookie : http://127.0.0.1/header/lab2-cookies/01_display_cookie_with_js.html

Pour information l'ajout de l'en-tête a été configuré dans Apache 2 :

<FilesMatch "(16_clear_site_storage.html)$">
    Header always set Clear-Site-Data "\"*\""
</FilesMatch>