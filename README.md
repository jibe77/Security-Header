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

TODO : exemple sur Secure
TODO : exemple sur HttpOnly

L'attribut Domain spécifie quels hôtes peuvent recevoir un cookie. S'il n'est pas spécifié, l'attribut utilise par défaut le même hôte qui a défini le cookie, à l'exclusion des sous-domaines. Si Domain est spécifié, les sous-domaines sont toujours inclus. Par conséquent, spécifier Domain est moins restrictif que de l'omettre.

TODO : exemple sur Domain

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

L'attribut SameSite permet aux serveurs de spécifier si/quand les cookies sont envoyés avec des requêtes intersites (où Site est défini par le domaine enregistrable et le schéma : http ou https). Cela offre une certaine protection contre les attaques de falsification de requêtes intersites (CSRF). Il prend trois valeurs possibles : Strict, Lax et None.

  Set-Cookie: mykey=myvalue; SameSite=Strict

Avec Strict, le cookie n'est envoyé qu'au site d'où il provient. Lax est similaire, sauf que les cookies sont envoyés lorsque l'utilisateur navigue sur le site d'origine du cookie. Par exemple, en suivant un lien depuis un site externe. Aucun spécifie que les cookies sont envoyés à la fois sur les requêtes d'origine et intersites, mais uniquement dans des contextes sécurisés (c'est-à-dire, si SameSite=None, l'attribut Secure doit également être défini). Si aucun attribut SameSite n'est défini, le cookie est traité comme Lax.

## Gestion de la session

Une session doit être automatiquement terminée sur le client lorsque l'utilisateur ferme le navigateur en créant des cookies sans date d'expiration.

Un cookie sans date d'expiration spécifiée expirera à la fermeture du navigateur.

Voici un exemple :

  Cookie: user_id=5; remember_me=true

TODO : exemple entre un cookie avec date et sans date.

## Gestion du cache

Afin de ne pas stocker le contenu dans le cache du navigateur, il est conseillé d'utiliser les en-têtes suivants : 

 Cache-Control: no-store

Pour les fichiers de l'application qui ne seront pas modifiés, vous pouvez généralement ajouter une mise en cache pour une semaine (soit 604800 secondes) :

 Cache-Control: public, max-age=604800, immutable 

Lorsque l'utilisateur se déconnecte, la suppression du cache, des cookies et du storage est fait via une réponse HTTP contenant cet en-tête :

 Clear-Site-Storage: "cache", "cookies", "storage"

TODO : exemple pour chacun.