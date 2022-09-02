# Security-Header

Ce document a pour but de recommander l'utilisation d'en-têtes en vue d'améliorer la sécurité d'une application Web. Celles-ci vont indiquer au navigateur Web ce que l'utilisateur a le droit de faire, ou pas. L'intérêt est de bloquer les utilisations abusives que pourraient exploiter des attaquants notamment via :
- l'injection de code JavaScript exécuté par d'autres utilisateurs en vue de fuiter des données (attaques XSS)
- l'injection de données à l'insue d'utilisateurs (attaque ClickJacking et CSRF)
- la récupération de données en clair sur le réseau (également appelé Sniffing)

Dans un premier temps nous présenterons :
- l'utilisation de l'en-tête Content-Security-Policy afin de contrôler les interactions de l'applications avec les ressources externes
- Strict-Transport-Security afin de forcer l'utilisation de HTTPS
- Configuration de l'accès au cookie
- Gestion de la session
- Gestion du cache
- X-XSS-Protection ?
