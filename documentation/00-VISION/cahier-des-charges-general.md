# WARISS.DEV — Cahier des charges général
## Spécifications fonctionnelles et techniques de référence

**Version :** 1.1  
**Date :** 21 novembre 2026 (v1.0 du 20 novembre 2026)  
**Statut :** Document de cadrage global — base de référence pour la conception détaillée  
**Projet :** WARISS.DEV

> **Version 1.1 :** ce document est désormais l'**unique cahier des charges de référence**. Il intègre les éléments du premier cahier des charges (14 novembre 2026) qui n'y figuraient pas : pages Now et Uses, thème clair/sombre, commandes du terminal, contact de style terminal avec alternative accessible et découpage en lots de livraison. Les décisions D-01 à D-10 du 21 novembre 2026 sont reportées dans les sections concernées et détaillées dans [l'architecture technique, section 8](../03-TECHNICAL-ARCHITECTURE/architecture-technique.md#8-registre-des-décisions). Ancienne version archivée : [99-ARCHIVE](../99-ARCHIVE/cahier-des-charges-general_2026-11-14.md).

---

# 1. Présentation générale du projet

## 1.1 Contexte

WARISS.DEV est une plateforme personnelle professionnelle destinée à présenter l'identité, le parcours, les compétences, les réalisations, les services et les connaissances de son propriétaire.

Le projet ne doit pas être limité à un portfolio statique. Il doit réunir un site public moderne et une administration privée permettant de gérer les contenus au moyen d'un CMS personnalisé.

La plateforme doit servir à la fois de :

- vitrine professionnelle ;
- portfolio de projets ;
- présentation des compétences et technologies ;
- présentation des services proposés ;
- espace de publication d'articles et de ressources ;
- laboratoire de démonstrations et d'expérimentations techniques ;
- espace de communication avec les visiteurs ;
- outil de gestion de contenus ;
- outil de suivi de l'activité et des statistiques.

Le site doit pouvoir évoluer au fil du temps sans nécessiter une réécriture générale de son architecture.

## 1.2 Vision

Construire une plateforme personnelle complète, professionnelle, soignée et évolutive, capable de présenter le propriétaire comme développeur, concepteur de solutions numériques et prestataire de services.

Le site doit mettre en valeur les compétences et les réalisations de manière crédible. Les contenus publiés doivent refléter les projets réellement réalisés, les responsabilités effectivement exercées et les technologies réellement utilisées.

## 1.3 Publics cibles

La plateforme s'adresse principalement à :

1. **Recruteurs et employeurs :** découvrir le profil, les compétences, les expériences, les projets et télécharger le CV.
2. **Clients et entreprises :** comprendre les services proposés, consulter les réalisations et prendre contact.
3. **Partenaires et collaborateurs :** découvrir le parcours, les domaines d'expertise et les possibilités de collaboration.
4. **Développeurs et apprenants :** consulter les articles, tutoriels, démonstrations et expérimentations techniques.
5. **Visiteurs généraux :** découvrir l'identité, les activités et les contenus publiés.

## 1.4 Objectifs principaux

- Présenter une identité professionnelle cohérente.
- Mettre en valeur les compétences et les expériences.
- Présenter des projets avec des informations structurées et vérifiables.
- Obtenir des opportunités professionnelles et commerciales.
- Publier des connaissances et partager des expériences.
- Démontrer les capacités techniques à travers des outils interactifs.
- Gérer les contenus depuis une administration centralisée.
- Suivre l'activité du site et mesurer les interactions utiles.
- Disposer d'une architecture sécurisée, maintenable et extensible.

---

# 2. Principes généraux du produit

## 2.1 Un site public associé à un CMS personnalisé

WARISS.DEV comportera deux grands espaces.

### Site public

C'est la partie accessible aux visiteurs. Elle affiche les contenus publiés : profil, services, compétences, expériences, projets, articles, réalisations, laboratoire et moyens de contact.

### Administration privée

C'est l'espace réservé aux utilisateurs autorisés. Il permet de créer, modifier, organiser, prévisualiser, publier, dépublier et archiver les contenus.

L'administration ne doit pas obliger à modifier le code pour les opérations éditoriales courantes.

## 2.2 CMS hybride

Le CMS sera hybride et spécialisé pour WARISS.DEV :

- Les pages importantes disposeront de structures frontend conçues et développées spécifiquement.
- Les textes, images, listes et données seront administrables.
- Certaines sections pourront être activées, désactivées ou réordonnées.
- Les contenus structurés seront alimentés par leurs propres modules : projets, compétences, expériences, articles, services, etc.
- Une possibilité de créer des pages personnalisées pourra être ajoutée ultérieurement, sans imposer dès la première version un constructeur visuel entièrement libre.

Le CMS ne doit pas devenir un système où chaque élément visuel est arbitrairement construit en base de données. La qualité du rendu, la cohérence du design, les performances et l'accessibilité restent prioritaires.

## 2.3 Séparation des responsabilités

Trois notions doivent rester distinctes :

- **Navigation :** menus et liens qui permettent de parcourir le site.
- **Pages et sections :** organisation et présentation des écrans publics.
- **Contenus :** informations réellement affichées dans les pages.

Exemple : le menu « Projets » renvoie vers la page `/projects`. Cette page affiche les projets publiés depuis le module Portfolio. Le menu ne stocke donc pas les projets et ne définit pas leur contenu.

## 2.4 Principes de conception des données

- Une information de référence ne doit pas être dupliquée inutilement.
- Les technologies sont enregistrées dans un catalogue central et réutilisées.
- Les médias sont gérés dans un système central.
- Les données privées et publiques doivent être séparées lorsque nécessaire.
- Les relations entre domaines doivent être cohérentes.
- Les suppressions doivent préserver l'intégrité des données.
- Les champs facultatifs ne doivent pas produire de blocs vides sur le site.
- Les contenus doivent pouvoir être publiés progressivement.
- Les besoins de la première version doivent être distingués des extensions futures.

---

# 3. Périmètre fonctionnel global

Le projet est organisé en 18 domaines fonctionnels. Ces 18 domaines constituent le périmètre de référence validé pour WARISS.DEV.

La validation des domaines signifie que leurs besoins doivent être étudiés et couverts. Elle ne signifie pas que chaque table, entité, colonne ou relation candidate est déjà définitivement validée. Le modèle de données sera affiné lors de la conception de la base de données.

---

# 4. Domaine 1 — Identité et profil personnel

## 4.1 Objectif

Présenter l'identité professionnelle et les informations publiques du propriétaire du site, tout en séparant ces informations du compte privé utilisé pour se connecter à l'administration.

## 4.2 Fonctionnalités publiques

La plateforme doit pouvoir présenter :

- nom et prénom ;
- nom professionnel ou signature ;
- titre ou fonction professionnelle ;
- courte présentation ;
- biographie détaillée ;
- photo de profil ;
- image de couverture, si nécessaire ;
- localisation publique, si souhaitée ;
- adresse e-mail professionnelle publique ;
- disponibilité professionnelle ;
- langues de communication ;
- liens vers les réseaux sociaux et plateformes professionnelles.

Les informations sensibles liées à l'authentification ne doivent jamais être exposées dans le profil public.

## 4.3 Administration

L'administrateur autorisé doit pouvoir modifier les informations du profil, gérer les liens sociaux, contrôler leur visibilité et leur ordre d'affichage.

## 4.4 Entités candidates

- `users` : comptes permettant l'authentification à l'administration.
- `profiles` : informations publiques du profil.
- `social_links` : liens vers les réseaux sociaux et plateformes externes.

## 4.5 Relations envisagées

- Un compte utilisateur possède un profil, selon le modèle retenu.
- Un profil peut posséder plusieurs liens sociaux.

La séparation entre compte privé et profil public doit être conservée.

---

# 5. Domaine 2 — Configuration générale du site

## 5.1 Objectif

Permettre de gérer les paramètres globaux du site depuis l'administration.

## 5.2 Paramètres envisagés

- nom du site ;
- slogan ;
- description générale ;
- logo ;
- favicon ;
- langue par défaut ;
- langues disponibles ;
- adresse de contact publique ;
- identité visuelle et couleurs globales ;
- préférences d'affichage globales ;
- informations du pied de page ;
- réglages généraux du référencement ;
- paramètres d'affichage et de maintenance.

Les paramètres réellement nécessaires seront définis précisément pendant la conception.

## 5.3 Gestion des menus

L'administration doit permettre de gérer plusieurs menus distincts, par exemple :

- menu principal ;
- menu de pied de page ;
- menu secondaire ;
- menus complémentaires futurs.

Pour chaque menu, l'administrateur doit pouvoir gérer son nom, son emplacement, son statut et ses éléments.

Chaque élément de navigation pourra contenir :

- libellé ;
- destination interne ou URL externe ;
- icône facultative ;
- ordre d'affichage ;
- visibilité ;
- ouverture dans un nouvel onglet, si nécessaire.

L'ordre des éléments doit être modifiable simplement.

## 5.4 Entités candidates

- `site_settings`
- `navigation_menus`
- `navigation_items`

## 5.5 Relation envisagée

Un menu contient plusieurs éléments de navigation. Les paramètres généraux du site constituent une configuration distincte.

---

# 6. Domaine 3 — Services professionnels

## 6.1 Objectif

Présenter les prestations réellement proposées et permettre leur gestion complète depuis le CMS.

Exemples indicatifs, à confirmer selon les offres réellement commercialisées :

- création de sites web professionnels ;
- développement d'applications web ;
- création de plateformes e-commerce ;
- développement backend et API REST ;
- création de tableaux de bord ;
- maintenance et amélioration d'applications.

## 6.2 Gestion des catégories

L'administrateur doit pouvoir créer, modifier, ordonner, activer et désactiver des catégories de services.

Exemples : Développement web, E-commerce, Backend et API, Maintenance.

## 6.3 Gestion des services

Un service pourra contenir :

- titre ;
- slug ;
- résumé ;
- description détaillée ;
- catégorie ;
- image principale ;
- médias complémentaires ;
- caractéristiques ;
- technologies associées ;
- bénéfices et éléments inclus ;
- public cible, si utile ;
- tarif indicatif facultatif ;
- type de tarification éventuel ;
- délai estimatif facultatif ;
- appel à l'action ;
- ordre d'affichage ;
- mise en avant ;
- état de publication ;
- informations SEO.

Les tarifs, délais et garanties ne doivent être affichés que s'ils ont été renseignés et validés.

## 6.4 Caractéristiques

Chaque service peut comporter plusieurs caractéristiques, avantages ou éléments inclus. L'administrateur peut les ajouter, modifier, ordonner, masquer ou supprimer.

## 6.5 Technologies

Les services peuvent être associés aux technologies du catalogue central. Il ne faut pas créer une nouvelle fiche de technologie dans ce module.

## 6.6 Affichage public

Une page liste les services publiés. Chaque service peut avoir une page de détail et un appel à l'action vers Contact ou une demande de devis.

Certains services pourront être mis en avant sur la page d'accueil.

## 6.7 Entités candidates

- `service_categories`
- `services`
- `service_features`
- table pivot de liaison entre services et technologies, par exemple `service_technology`

Les médias, métadonnées SEO et demandes de contact devront réutiliser les domaines centraux correspondants.

---

# 7. Domaine 4 — Technologies

## 7.1 Objectif

Créer un catalogue central des technologies, outils, langages, frameworks, bibliothèques et plateformes maîtrisés ou utilisés dans les projets.

Exemples : JavaScript, TypeScript, PHP, Laravel, React, Vue.js, Node.js, PostgreSQL, MySQL, MongoDB, Docker.

## 7.2 Informations possibles

- nom ;
- slug ;
- description ;
- logo ou icône ;
- catégorie ;
- site officiel ;
- niveau ou statut de maîtrise, si cette notion est retenue ;
- ordre d'affichage ;
- visibilité ;
- statut actif.

## 7.3 Catégories possibles

- langages ;
- frameworks ;
- bibliothèques ;
- bases de données ;
- outils ;
- plateformes ;
- technologies de déploiement et infrastructure.

## 7.4 Réutilisation

Une technologie doit être enregistrée une seule fois puis réutilisée dans :

- projets ;
- compétences ;
- expériences ;
- services ;
- expérimentations techniques ;
- formations ou contenus, si pertinent.

Les associations doivent être modélisées par des relations ou tables pivots adaptées.

## 7.5 Entités candidates

- `technology_categories`
- `technologies`
- tables pivots selon les domaines qui utilisent les technologies.

---

# 8. Domaine 5 — Compétences

## 8.1 Objectif

Présenter les compétences techniques et professionnelles, en distinguant une compétence d'une simple technologie.

Exemples de compétences :

- conception d'API REST ;
- conception de bases de données relationnelles ;
- développement d'interfaces responsives ;
- intégration de services backend ;
- conception d'applications web ;
- gestion de versions avec Git.

## 8.2 Informations possibles

- nom ;
- description ;
- catégorie ;
- niveau ou degré de maîtrise, si utilisé avec prudence ;
- ordre d'affichage ;
- visibilité ;
- technologies associées ;
- projets illustrant la compétence, si pertinent.

## 8.3 Catégories possibles

- frontend ;
- backend ;
- bases de données ;
- conception et architecture ;
- outils et méthodes ;
- compétences professionnelles.

## 8.4 Règle importante

Une compétence décrit ce que le propriétaire sait réaliser. Une technologie désigne un outil ou environnement utilisé pour le réaliser. Les deux notions ne doivent pas être confondues.

## 8.5 Entités candidates

- `skill_categories`
- `skills`
- tables pivots pour les associations avec technologies et projets, si confirmées.

---

# 9. Domaine 6 — Portfolio et projets

## 9.1 Objectif

Présenter les projets réalisés, en cours ou expérimentaux, afin de démontrer l'expérience et les capacités techniques.

Les projets publiés doivent être décrits honnêtement. Le rôle réellement exercé, les fonctionnalités développées et les technologies utilisées doivent être clairement indiqués.

## 9.2 Informations d'un projet

Un projet pourra comporter :

- nom ;
- slug ;
- résumé ;
- description ;
- contexte et objectif ;
- rôle et responsabilités ;
- statut du projet ;
- date ou période ;
- image de couverture ;
- galerie d'images ;
- URL de démonstration ;
- URL du dépôt de code, si public ;
- catégories ;
- fonctionnalités ;
- technologies ;
- visibilité ;
- mise en avant ;
- informations SEO.

Les liens privés ou confidentiels ne doivent pas être exposés publiquement.

## 9.3 Fonctionnalités

Chaque projet pourra présenter plusieurs fonctionnalités ou modules. Ceux-ci pourront être ordonnés et décrits individuellement.

## 9.4 Catégories

Les projets pourront être organisés selon leur nature : application web, e-commerce, plateforme éducative, outil de gestion, application mobile, etc.

## 9.5 Administration

L'administrateur pourra créer, modifier, prévisualiser, publier, dépublier, archiver et mettre en avant les projets.

## 9.6 Entités candidates

- `project_categories`
- `projects`
- `project_features`
- tables pivots projets-technologies et projets-compétences, si retenues.

---

# 10. Domaine 7 — Études de cas

## 10.1 Objectif

Permettre de présenter certaines réalisations de manière approfondie, au-delà d'une simple carte de portfolio.

Une étude de cas doit expliquer le problème, le contexte, la démarche, les choix techniques, les solutions mises en place et les résultats réellement observés.

## 10.2 Contenu possible

- titre ;
- résumé ;
- projet associé ;
- contexte ;
- problème ou besoin initial ;
- objectifs ;
- contraintes ;
- défis rencontrés ;
- solutions proposées ;
- architecture ou démarche ;
- résultats ;
- enseignements ;
- captures et médias ;
- technologies utilisées ;
- dates ;
- informations SEO.

Les résultats chiffrés ne doivent être affichés que s'ils sont vérifiables.

## 10.3 Relations

Une étude de cas peut être rattachée à un projet. Un projet peut avoir zéro ou plusieurs études de cas, selon le besoin réel.

## 10.4 Entités candidates

- `case_studies`
- structures de défis et solutions, si leur séparation apporte une réelle valeur ;
- relation avec `projects` ;
- réutilisation du système central de médias et technologies.

Les entités `challenges` et `solutions` restent à étudier : elles ne sont pas automatiquement imposées comme tables distinctes.

---

# 11. Domaine 8 — Parcours professionnel

## 11.1 Objectif

Présenter le parcours professionnel, les expériences, les formations et les étapes importantes du développement des compétences.

## 11.2 Expériences professionnelles

Informations possibles :

- poste ou fonction ;
- organisation ;
- lieu ;
- type d'expérience ;
- description ;
- responsabilités ;
- réalisations ;
- date de début ;
- date de fin ;
- expérience en cours ;
- technologies associées ;
- visibilité ;
- ordre d'affichage.

## 11.3 Formations et études

Informations possibles :

- établissement ;
- diplôme ou formation ;
- domaine ;
- description ;
- date de début ;
- date de fin ;
- statut ;
- lieu ;
- justificatif ou média facultatif.

Les informations doivent être exactes et ne pas laisser entendre qu'un diplôme a été obtenu lorsqu'il ne l'a pas été.

## 11.4 Entités candidates

- `experiences`
- `education` ou `educations`

Les relations avec les technologies doivent réutiliser le catalogue central.

---

# 12. Domaine 9 — Certifications et réalisations

## 12.1 Objectif

Présenter les certifications, attestations, récompenses, distinctions et réalisations significatives.

## 12.2 Informations possibles

- titre ;
- type ;
- organisme émetteur ;
- description ;
- date d'obtention ;
- date d'expiration, si applicable ;
- identifiant de vérification, si public ;
- URL de vérification ;
- image ou document justificatif ;
- visibilité ;
- ordre d'affichage.

## 12.3 Réalisations

Les réalisations peuvent représenter des résultats, distinctions ou accomplissements qui ne sont pas nécessairement des certifications.

Il faut éviter de confondre un projet, une expérience et une certification.

## 12.4 Entités candidates

- `certifications`
- `achievements`

---

# 13. Domaine 10 — Chronologie personnelle

## 13.1 Objectif

Présenter une chronologie visuelle des événements importants du parcours personnel et professionnel.

Exemples :

- début d'une formation ;
- obtention d'une certification ;
- début d'une expérience ;
- lancement d'un projet ;
- participation à un événement ;
- étape importante d'apprentissage.

## 13.2 Informations possibles

- titre ;
- description ;
- date ou période ;
- type d'événement ;
- image facultative ;
- ordre ;
- visibilité ;
- référence éventuelle à une expérience, une formation, un projet ou une certification.

## 13.3 Règle de non-duplication

La chronologie ne doit pas devenir une copie complète des autres modules. Lorsqu'un événement correspond à une expérience ou une formation déjà enregistrée, il faudra privilégier une référence vers la donnée source ou un mécanisme de présentation dérivé.

## 13.4 Entité candidate

- `timeline_events`

La stratégie exacte de liaison et de génération des événements sera décidée pendant la conception.

---

# 14. Domaine 11 — Laboratoire technique

## 14.1 Objectif

Présenter des expérimentations, démonstrations, prototypes et petits outils interactifs qui montrent la curiosité et les capacités techniques du propriétaire.

## 14.2 Types de contenus possibles

- démonstrations frontend ;
- tests d'API ;
- prototypes ;
- visualisations ;
- mini-outils interactifs ;
- expérimentations UI/UX ;
- explorations de technologies ;
- projets d'apprentissage.

## 14.3 Informations possibles

- titre ;
- slug ;
- résumé ;
- description ;
- objectif de l'expérience ;
- catégorie ;
- technologies utilisées ;
- images ou vidéo ;
- URL de démonstration ;
- dépôt de code ;
- date ;
- statut ;
- visibilité ;
- notes techniques.

## 14.4 Administration

L'administrateur doit pouvoir créer, modifier, organiser, publier, dépublier et archiver les expérimentations.

Les démonstrations exécutées sur le site devront être isolées et sécurisées. Aucun code non fiable ne doit être exécuté librement dans le contexte principal du site.

## 14.5 Entités candidates

- `experiment_categories`
- `experiments`
- associations aux technologies et médias.

---

# 15. Domaine 12 — Blog et partage de connaissances

## 15.1 Objectif

Permettre de publier des articles, tutoriels, retours d'expérience, explications techniques, conseils et réflexions professionnelles.

## 15.2 Types de contenus

- articles ;
- tutoriels ;
- guides ;
- retours d'expérience ;
- notes techniques ;
- conseils ;
- actualités personnelles ou professionnelles.

## 15.3 Informations d'un article

- titre ;
- slug ;
- résumé ;
- contenu ;
- image de couverture ;
- auteur ;
- catégorie ;
- tags ;
- date de création ;
- date de publication ;
- date de mise à jour ;
- temps de lecture estimé, si calculé ;
- statut ;
- mise en avant ;
- informations SEO.

## 15.4 Organisation

Les articles peuvent être associés à une ou plusieurs catégories selon la règle retenue, et à plusieurs tags.

L'administration doit permettre la rédaction, la prévisualisation, l'enregistrement en brouillon, la programmation éventuelle, la publication, la modification et l'archivage.

## 15.5 Affichage public

- page de liste des articles ;
- page de détail ;
- navigation entre articles ;
- filtres ou recherche, si retenus ;
- affichage des catégories et tags ;
- partage sur les réseaux sociaux.

## 15.6 Entités candidates

- `posts`
- `post_categories`
- `tags`
- tables pivots articles-tags et, si nécessaire, articles-catégories.

La gestion de commentaires publics n'est pas obligatoire dans la première version et devra être décidée séparément.

---

# 16. Domaine 13 — Communication

## 16.1 Objectif

Permettre aux visiteurs de communiquer avec le propriétaire du site et de recueillir les témoignages ou inscriptions utiles.

## 16.2 Formulaire de contact

Le visiteur pourra envoyer un message comprenant selon le besoin :

- nom ;
- adresse e-mail ;
- sujet ;
- message ;
- service concerné, facultatif ;
- consentement aux informations de confidentialité, si nécessaire.

Les messages devront être stockés et consultables dans l'administration.

Une expérience originale est prévue : une **interface inspirée d'un terminal** dans laquelle le visiteur renseigne les informations étape par étape. Un **formulaire classique accessible** doit toujours être proposé en alternative, avec le même traitement côté serveur (validation, enregistrement, protection anti-spam). Les deux interfaces envoient la même requête à la même route de l'API.

Le système doit valider les données, afficher des erreurs compréhensibles, confirmer l'envoi et être protégé contre le spam et les abus.

## 16.3 Gestion des messages

L'administrateur pourra :

- consulter les messages ;
- rechercher et filtrer ;
- marquer comme lu ou non lu ;
- changer leur état de traitement ;
- archiver ou supprimer selon la politique de conservation ;
- consulter les informations de contexte utiles.

Les notifications par e-mail pourront être ajoutées sans remplacer l'enregistrement du message dans le système.

## 16.4 Témoignages

L'administration pourra gérer des témoignages reçus de clients ou collaborateurs.

Un témoignage pourra contenir :

- nom de la personne ;
- fonction ou organisation, si autorisée ;
- texte ;
- photo facultative ;
- date ;
- statut de validation ;
- visibilité ;
- ordre d'affichage.

Un témoignage ne doit être publié qu'avec l'autorisation appropriée.

## 16.5 Newsletter

La plateforme pourra permettre à un visiteur de s'inscrire à une newsletter.

Fonctionnalités envisagées :

- inscription ;
- confirmation de l'adresse, si retenue ;
- désinscription ;
- gestion du statut ;
- conservation de la date de consentement ;
- gestion des demandes de suppression.

L'envoi de campagnes peut être développé dans une étape ultérieure ou confié à un service spécialisé.

## 16.6 Demandes de devis

Les pages de services pourront diriger vers un formulaire de contact ou de demande de devis. Les demandes seront gérées dans ce domaine ou dans une extension dédiée, sans dupliquer le formulaire général.

## 16.7 Entités candidates

- `messages`
- `testimonials`
- `newsletter_subscribers`
- `contact_requests`, si un traitement distinct des demandes de devis est justifié
- `newsletter_campaigns`, uniquement si la gestion des campagnes est incluse dans le périmètre.

Les trois dernières entités doivent être étudiées avant d'être définitivement intégrées.

---

# 17. Domaine 14 — Gestion des médias

## 17.1 Objectif

Centraliser les fichiers utilisés dans les différentes parties du site afin d'éviter les duplications et de simplifier leur gestion.

## 17.2 Types de médias

- images ;
- logos ;
- icônes ;
- documents ;
- CV ;
- certificats ;
- fichiers téléchargeables ;
- vidéos ou liens vidéo, selon le mode de stockage retenu.

## 17.3 Fonctionnalités d'administration

- téléverser un fichier ;
- consulter les médias ;
- rechercher et filtrer ;
- modifier les métadonnées ;
- renseigner un texte alternatif ;
- organiser en collections ou dossiers logiques ;
- réutiliser un média dans plusieurs contenus ;
- remplacer ou supprimer un média après vérification de ses utilisations.

## 17.4 Informations possibles

- nom original ;
- nom de stockage ;
- chemin ou clé de stockage ;
- type MIME ;
- extension ;
- taille ;
- dimensions pour les images ;
- texte alternatif ;
- légende ;
- description ;
- auteur ou source, si nécessaire ;
- date d'ajout ;
- métadonnées techniques.

## 17.5 Stockage

Le système devra pouvoir utiliser un stockage local en développement et un stockage objet compatible S3 en production, selon la configuration.

Les fichiers ne doivent pas être enregistrés directement comme gros contenus binaires dans les tables métier sans justification.

## 17.6 Entités candidates

- `media`
- `media_collections`

La relation entre les médias et les autres contenus pourra utiliser une relation polymorphique Laravel ou des tables pivots dédiées. Cette décision sera prise après étude des contraintes et des besoins de requêtage.

---

# 18. Domaine 15 — Gestion du CV

## 18.1 Objectif

Permettre de gérer un ou plusieurs CV et de proposer leur consultation ou téléchargement.

## 18.2 Fonctionnalités

- enregistrer un CV ;
- téléverser le fichier PDF ;
- renseigner son titre ou sa version ;
- définir la langue ;
- préciser le type ou la cible du CV ;
- définir le statut ;
- choisir le CV mis en avant ou proposé par défaut ;
- remplacer une version ;
- conserver un historique si nécessaire ;
- permettre le téléchargement public du CV autorisé.

## 18.3 Règles

Les fichiers publics doivent être explicitement sélectionnés. Les anciennes versions ou documents privés ne doivent pas être accessibles par erreur.

## 18.4 Entités candidates

- `resumes`
- `resume_versions`, si la gestion d'un historique de versions séparé est nécessaire.

Le lien vers le fichier devra réutiliser le système central des médias lorsque cela est pertinent.

---

# 19. Domaine 16 — Administration, sécurité et référencement

## 19.1 Objectif

Protéger l'administration, contrôler les accès, suivre les actions importantes et gérer les paramètres techniques et SEO du site.

## 19.2 Authentification

- connexion sécurisée ;
- déconnexion ;
- gestion des sessions ou jetons ;
- réinitialisation du mot de passe ;
- vérification des données d'authentification ;
- limitation des tentatives abusives ;
- protection des routes privées.

L'authentification de l'administration utilisera Laravel Sanctum selon le mode d'intégration retenu.

## 19.3 Utilisateurs et permissions

Le système devra pouvoir évoluer vers plusieurs utilisateurs administrateurs.

Il pourra distinguer des rôles tels que :

- administrateur ;
- éditeur ;
- contributeur.

Les permissions devront contrôler les actions possibles : consulter, créer, modifier, publier, supprimer ou gérer les paramètres.

Pour la première version, un seul administrateur peut suffire, mais la conception ne doit pas rendre impossible l'évolution.

**Décision D-04 (21/11/2026) :** la première version utilise une colonne `role` sur `users` avec deux valeurs : `admin` (tous les droits) et `viewer` (lecture seule, sans accès aux données sensibles). Il n'y a pas de tables `roles` et `permissions` en V1. Toutes les autorisations passent par les Policies Laravel, ce qui permettra de migrer vers des rôles et permissions en base (par exemple `spatie/laravel-permission`) sans toucher aux contrôleurs, si des rôles éditeur ou contributeur deviennent nécessaires.

## 19.4 Journal d'activité

Les actions importantes pourront être historisées :

- connexion ;
- création ;
- modification ;
- publication ;
- dépublication ;
- suppression ;
- modification de paramètres ;
- changement de permissions.

Le journal doit enregistrer les informations utiles sans stocker de secrets ni de mots de passe.

## 19.5 SEO global

Le système doit permettre de gérer :

- titres SEO ;
- méta-descriptions ;
- slugs ;
- URL canoniques ;
- indexation ou non-indexation ;
- données de partage social ;
- redirections ;
- sitemap ;
- robots.txt ;
- métadonnées structurées lorsque pertinentes.

Les métadonnées peuvent être gérées de manière centralisée ou associées aux contenus selon le modèle retenu.

## 19.6 Sécurité applicative

- validation côté backend ;
- contrôle des permissions ;
- protection contre les injections ;
- protection contre les attaques XSS et CSRF selon le mode d'authentification ;
- gestion sûre des téléversements ;
- configuration sécurisée des secrets ;
- journalisation sans données sensibles ;
- sauvegardes et procédure de restauration ;
- limitation des abus sur les formulaires et l'authentification.

## 19.7 Entités candidates

- ~~`roles`, `permissions` et leurs tables pivots~~ : remplacés en V1 par la colonne `users.role` (décision D-04) ;
- `activity_logs`
- `seo_metadata`
- `redirects`

Les tables natives et conventions Laravel/Sanctum devront être prises en compte avant de créer des entités personnalisées.

---

# 20. Domaine 17 — Statistiques et analytics

## 20.1 Objectif

Fournir des indicateurs permettant de comprendre l'utilisation du site et l'intérêt porté aux contenus.

## 20.2 Indicateurs possibles

- nombre de visites ;
- pages consultées ;
- pages les plus consultées ;
- provenance du trafic, si disponible ;
- appareils ou catégories d'appareils, de manière agrégée ;
- clics sur certains appels à l'action ;
- téléchargements du CV ;
- consultations des projets ;
- consultations des services ;
- envois de formulaires ;
- évolution de l'activité sur une période.

## 20.3 Administration

Un tableau de bord pourra afficher des indicateurs, graphiques, périodes et contenus les plus consultés.

## 20.4 Respect de la vie privée

La collecte doit être limitée aux données réellement nécessaires. Les règles de consentement, de conservation, d'anonymisation et de confidentialité devront être définies selon les outils utilisés et les obligations applicables.

Il faut éviter de collecter des données personnelles inutiles ou de conserver des informations identifiantes sans justification.

## 20.5 Entités candidates

- `page_views`
- `events`

Le schéma définitif dépendra de la stratégie analytics choisie. Une solution externe peut aussi être étudiée pour éviter de développer inutilement un système complet de suivi.

---

# 21. Domaine 18 — Pages, sections et expérience de navigation

## 21.1 Objectif

Gérer la structure des pages publiques et certaines parties de leur présentation depuis le CMS, sans perdre le contrôle du design et du code frontend.

Ce domaine formalise la dimension « pages et sections » du CMS hybride. Il doit être coordonné avec les domaines Configuration, Portfolio, Blog, Services et les autres modules de contenu.

## 21.2 Pages publiques prévues

La liste pourra évoluer, mais le périmètre de départ comprend notamment :

- Accueil ;
- À propos ;
- Compétences ;
- Expériences ;
- Projets / Portfolio ;
- Détail d'un projet ;
- Études de cas ;
- Services ;
- Détail d'un service ;
- CV ;
- Laboratoire ;
- Blog ;
- Détail d'un article ;
- Certifications et réalisations ;
- Contact ;
- Témoignages ;
- Terminal ou espace de démonstration interactif ;
- **Now** : ce sur quoi le propriétaire travaille actuellement (projets en cours, technologies étudiées, objectifs, apprentissages récents) ;
- **Uses** : outils, logiciels, équipements et environnement de travail ;
- confirmation et désinscription de la newsletter ;
- pages légales et pages d'information (mentions légales, confidentialité, cookies si nécessaire) ;
- page 404.

Toutes les pages ne doivent pas obligatoirement être enregistrées comme des pages libres en base de données. Certaines sont des routes frontend fixes qui affichent des données issues des modules correspondants.

Les pages **Now** et **Uses** sont des pages éditoriales gérées par le module Pages et sections (pages système `now` et `uses` composées de sections `richtext`), sans table dédiée.

## 21.2 bis Routes publiques

Décision D-03 (21/11/2026) : toutes les routes sont préfixées par la langue. Les segments de route restent en anglais et identiques dans les deux langues ; seuls les slugs de contenu sont traduits.

```text
/                          → redirection vers /fr ou /en
/:lang                     Accueil
/:lang/about
/:lang/skills
/:lang/experience
/:lang/projects            /:lang/projects/:slug
/:lang/case-studies/:slug
/:lang/services            /:lang/services/:slug
/:lang/lab                 /:lang/lab/:slug
/:lang/blog                /:lang/blog/:slug
/:lang/blog/category/:slug /:lang/blog/tag/:slug
/:lang/achievements
/:lang/testimonials
/:lang/resume
/:lang/contact
/:lang/terminal
/:lang/now
/:lang/uses
/:lang/newsletter/confirm  /:lang/newsletter/unsubscribe
/:lang/legal-notice        /:lang/privacy
/:lang/preview/:token      aperçu de brouillon (non indexé)
```

Exemple : `/fr/projects/plateforme-e-commerce` et `/en/projects/e-commerce-platform`.

## 21.2 ter Terminal interactif

Le terminal est une **simulation contrôlée côté navigateur** : aucune commande système n'est exécutée, ni sur le serveur ni chez le visiteur. Les réponses sont construites à partir des données publiques de l'API.

Commandes de départ : `help`, `whoami`, `about`, `skills`, `projects`, `experience`, `contact`, `resume`, `github`, `clear`. Le terminal gère les commandes inconnues, propose un historique (flèches haut et bas) et reste utilisable au clavier et avec un lecteur d'écran.

## 21.3 Gestion des sections

Pour les pages compatibles, l'administration pourra permettre :

- d'activer ou désactiver une section ;
- de modifier certains titres et textes ;
- de modifier certains paramètres de présentation autorisés ;
- de changer l'ordre des sections lorsque cela est prévu ;
- de choisir les contenus mis en avant ;
- de prévisualiser le résultat.

Exemple de sections de la page d'accueil :

1. Hero / présentation principale ;
2. À propos ;
3. Compétences ;
4. Projets sélectionnés ;
5. Expériences ;
6. Services ;
7. Articles récents ;
8. Appel à l'action ;
9. Contact ou pied de page.

Les sections dynamiques doivent utiliser les données de leurs modules sources. Il ne faut pas recopier manuellement les projets ou compétences dans une table de contenu de page.

## 21.4 Pages personnalisées

La création de pages personnalisées pourra être prévue comme une évolution. Elle devra utiliser des composants ou blocs maîtrisés, avec des règles de validation, plutôt qu'autoriser l'exécution de code arbitraire.

## 21.5 Entités candidates

- `pages`, si des pages éditoriales personnalisées sont nécessaires ;
- `page_sections` ou configuration de sections, si le contrôle dynamique des sections le justifie ;
- éventuellement des tables de liaison entre pages et contenus mis en avant.

Ces entités sont à étudier : elles ne doivent pas dupliquer les modules métier ni être créées uniquement pour reproduire le rendu frontend.

---

# 22. Fonctionnalités transversales

Les besoins suivants concernent plusieurs domaines et doivent être pensés globalement.

## 22.1 Recherche et filtrage

L'administration devra proposer, lorsque pertinent :

- recherche par nom ou titre ;
- filtres par statut ;
- filtres par catégorie ;
- tri par date ou ordre ;
- pagination ;
- actions groupées prudentes.

Le site public pourra proposer une recherche globale ou des filtres propres à certains modules si le besoin le justifie.

## 22.2 Brouillons, publication et archivage

Les contenus éditoriaux devront pouvoir suivre un cycle de vie adapté :

- brouillon ;
- publié ;
- archivé ;
- éventuellement programmé.

Les statuts réellement nécessaires seront définis par domaine. Il n'est pas obligatoire que toutes les tables utilisent exactement le même ensemble de statuts.

## 22.3 Prévisualisation

L'administrateur doit pouvoir vérifier le rendu d'un contenu avant sa publication, sans rendre un brouillon accessible publiquement.

Décision D-09 : l'aperçu passe par un lien signé et temporaire (30 minutes) généré par l'administration, affiché par le site public sur `/:lang/preview/:token`, jamais mis en cache et jamais indexé. Détails dans l'architecture technique, section 3.8.

## 22.4 Multilingue

Décision validée : **français (langue par défaut) et anglais**, avec des tables de traduction `<entité>_translations` communes à tous les domaines, des URLs `/fr/...` et `/en/...`, un SEO dans les deux langues et des onglets FR/EN dans l'administration.

Le changement de langue doit conserver la page consultée : chaque contenu public renvoie ses slugs dans les deux langues.

Il ne faut pas créer une solution multilingue différente dans chaque domaine.

## 22.5 Notifications

Des notifications internes ou par e-mail pourront être déclenchées pour certains événements :

- nouveau message ;
- nouvelle demande de devis ;
- inscription à la newsletter ;
- erreur ou événement administratif important.

Le système de notification devra être centralisé si le besoin devient suffisamment important.

## 22.6 Historique et traçabilité

Les modifications sensibles doivent pouvoir être suivies. La stratégie d'audit doit être proportionnée aux besoins et éviter de conserver des données inutiles.

## 22.7 Thème clair et sombre

Le visiteur peut choisir entre un thème clair et un thème sombre. Par défaut, le site public est en sombre et l'administration en clair. La préférence du système d'exploitation peut être respectée. Le choix est conservé dans un cookie lu par le serveur, pour éviter un flash de mauvais thème au chargement.

## 22.8 Accessibilité des expériences originales

Les expériences inspirées du terminal ou fortement visuelles doivent avoir une alternative accessible (formulaire de contact classique, navigation standard). Les animations respectent la préférence `prefers-reduced-motion`.

---

# 23. Administration CMS — Organisation fonctionnelle proposée

L'administration devra présenter une navigation claire, organisée par modules.

Exemple de structure indicative :

```text
Administration
├── Tableau de bord
├── Identité et profil
│   ├── Profil
│   └── Réseaux sociaux
├── Configuration
│   ├── Paramètres du site
│   ├── Menus
│   └── Pages et sections
├── Services
│   ├── Tous les services
│   └── Catégories
├── Technologies
│   ├── Technologies
│   └── Catégories
├── Compétences
│   ├── Compétences
│   └── Catégories
├── Portfolio
│   ├── Projets
│   ├── Catégories
│   └── Études de cas
├── Parcours
│   ├── Expériences
│   ├── Formations
│   └── Chronologie
├── Certifications et réalisations
├── Laboratoire
│   ├── Expérimentations
│   └── Catégories
├── Blog
│   ├── Articles
│   ├── Catégories
│   └── Tags
├── Communication
│   ├── Messages
│   ├── Témoignages
│   └── Newsletter
├── Médias
├── CV
├── Référencement
├── Analytics
└── Administration et sécurité
    ├── Utilisateurs
    ├── Rôles et permissions
    └── Journal d'activité
```

Cette organisation est indicative. Les intitulés et regroupements définitifs seront décidés pendant la conception UX/UI de l'administration.

---

# 24. Pages publiques — Organisation indicative

La navigation publique pourra proposer les entrées suivantes :

- Accueil ;
- À propos ;
- Compétences ;
- Expériences ;
- Projets ;
- Services ;
- Laboratoire ;
- Blog ;
- Certifications ;
- Contact.

Le CV pourra être accessible depuis un bouton dédié, par exemple « Télécharger mon CV ».

Un terminal interactif est proposé comme élément distinctif (voir 21.2 ter). Son fonctionnement et son niveau d'accès sont conçus pour ne pas exposer le serveur ou des informations privées.

Le menu ne doit pas nécessairement afficher toutes les pages. L'administrateur pourra contrôler les éléments de navigation.

---

# 25. Architecture technique retenue

Les choix suivants constituent la base technique de référence du projet.

## 25.1 Frontend

- React ;
- TypeScript ;
- architecture par composants ;
- interface responsive ;
- séparation entre site public et administration, tout en permettant une organisation cohérente du code ;
- consommation de l'API backend.

## 25.2 Backend

- Laravel ;
- API REST ;
- logique métier côté backend ;
- validation des données ;
- autorisations et permissions ;
- Laravel Sanctum pour l'authentification de l'administration ;
- Eloquent comme ORM.

## 25.3 Base de données

- PostgreSQL.

## 25.4 Médias et fichiers

- système de stockage central ;
- stockage local en développement ;
- stockage objet compatible S3 envisageable en production ;
- métadonnées des fichiers en base de données.

## 25.5 Cache et traitements asynchrones

Redis, files d'attente et traitements en arrière-plan pourront être ajoutés lorsque les besoins le justifient, notamment pour les e-mails, traitements de médias et tâches planifiées.

## 25.6 Architecture générale

Le frontend React/TypeScript communique avec le backend Laravel au moyen d'une API REST. Laravel applique les règles métier, contrôle les permissions et communique avec PostgreSQL. Les fichiers sont gérés par le système central de stockage.

Le frontend ne doit pas être considéré comme une couche de sécurité : toutes les validations et autorisations importantes doivent être appliquées par le backend.

---

# 26. Exigences non fonctionnelles

## 26.1 Design et expérience utilisateur

- identité visuelle professionnelle et cohérente ;
- interface moderne et soignée ;
- navigation intuitive ;
- animations mesurées ;
- hiérarchie visuelle claire ;
- expérience adaptée aux visiteurs et aux recruteurs ;
- administration simple à utiliser.

## 26.2 Responsive

Le site et l'administration doivent fonctionner sur :

- smartphones ;
- tablettes ;
- ordinateurs portables ;
- écrans de bureau.

## 26.3 Performance

- chargement rapide ;
- images optimisées ;
- pagination des listes volumineuses ;
- requêtes SQL maîtrisées ;
- mise en cache lorsque pertinente ;
- limitation des ressources inutiles.

## 26.4 Accessibilité

- structure HTML sémantique ;
- navigation au clavier ;
- contrastes suffisants ;
- textes alternatifs pertinents ;
- formulaires compréhensibles ;
- états et erreurs clairement communiqués.

## 26.5 Sécurité

- authentification robuste ;
- permissions côté serveur ;
- validation et nettoyage des entrées ;
- protection des formulaires ;
- gestion sûre des fichiers ;
- secrets hors du dépôt de code ;
- sauvegardes ;
- journalisation prudente ;
- mises à jour régulières des dépendances.

## 26.6 SEO

- URLs propres ;
- métadonnées adaptées ;
- sitemap ;
- balises canoniques ;
- partage social ;
- indexation contrôlée ;
- contenu structuré et accessible.

## 26.7 Maintenabilité et évolutivité

- code organisé par domaines ;
- responsabilités séparées ;
- conventions de nommage cohérentes ;
- documentation technique ;
- tests automatisés ;
- migrations versionnées ;
- possibilité d'ajouter des modules sans casser les autres.

## 26.8 Fiabilité et sauvegardes

La base de données et les fichiers devront faire l'objet de sauvegardes adaptées. Une procédure de restauration devra être prévue et testée.

---

# 27. Sécurité, confidentialité et conformité

Le projet devra limiter les données personnelles collectées aux informations nécessaires.

Les messages, inscriptions newsletter, statistiques et journaux d'activité devront être gérés selon des règles de conservation et d'accès adaptées.

Les visiteurs devront être informés des traitements pertinents au moyen des pages légales et informations de confidentialité nécessaires.

Les fonctionnalités d'analyse ou de suivi devront être configurées de manière proportionnée et respectueuse de la vie privée.

Les pages légales devront pouvoir être présentées sur le site. Leur contenu devra être rédigé et vérifié en fonction de la situation réelle du propriétaire et des obligations applicables.

---

# 28. Périmètre de la première version et évolutions futures

## 28.1 Première version fonctionnelle

La première version doit prioriser les fonctions nécessaires pour rendre la plateforme utile :

- identité et profil ;
- configuration du site et navigation ;
- pages publiques principales ;
- services ;
- technologies ;
- compétences ;
- portfolio et projets ;
- expériences et formations ;
- certifications et réalisations ;
- chronologie ;
- laboratoire ;
- blog ;
- contact ;
- médias ;
- CV ;
- administration et sécurité ;
- SEO de base ;
- statistiques essentielles.

Les modules pourront être développés par étapes, mais leur architecture devra être cohérente dès la conception.

## 28.1 bis Lots de livraison de la V1

Décision D-02 (21/11/2026) : le périmètre V1 ci-dessus est conservé en entier. L'ancien découpage en phases devient l'**ordre de livraison**. Toute la base de données est créée dès le lot 1 et le bilinguisme FR/EN est présent dès le départ, car l'ajouter après coup coûterait très cher. Le site n'est mis en ligne publiquement qu'à la fin du lot 1.

| Lot | Contenu | Objectif |
|---|---|---|
| **Lot 1 — Fondation** | Authentification et rôles, médias, profil, configuration et menus, pages et sections, technologies, compétences, services, portfolio et études de cas, parcours (expériences, formations), CV, contact (classique + terminal), SEO de base, pages légales, thème clair/sombre, FR/EN | Site professionnel complet, publiable |
| **Lot 2 — Contenus** | Blog (catégories, tags), certifications et réalisations, chronologie, témoignages, pages Now et Uses | Présence éditoriale et crédibilité |
| **Lot 3 — Interactif** | Laboratoire et démonstrations, terminal interactif, animations avancées | Différenciation technique |
| **Lot 4 — Suivi** | Statistiques essentielles et tableau de bord, inscription à la newsletter (double confirmation), recherche plein texte publique | Mesure et fidélisation |

L'envoi de campagnes de newsletter (`newsletter_campaigns`) est prévu dans le modèle mais développé **après la V1**.

## 28.2 Évolutions possibles

Selon les besoins réels :

- plusieurs administrateurs et permissions avancées ;
- workflow éditorial ;
- pages personnalisées ;
- constructeur de sections plus flexible ;
- newsletter et campagnes ;
- analytics plus avancé ;
- recherche globale ;
- formulaires de devis avancés ;
- espace client ;
- paiement en ligne ;
- facturation ;
- prise de rendez-vous ;
- API publique ;
- intégrations externes.

Ces évolutions ne doivent pas être ajoutées automatiquement à la première version. Elles devront faire l'objet de besoins et de décisions spécifiques.

---

# 29. Règles de conception de la base de données

Cette partie prépare la phase suivante sans figer prématurément le schéma.

## 29.1 Ne pas confondre domaine, entité et table

Un domaine représente un ensemble fonctionnel. Une entité représente un objet métier. Une table est une solution de stockage relationnelle choisie pour représenter une ou plusieurs notions selon les règles de conception.

La liste des entités candidates ci-dessous est donc un point de départ, pas une liste de migrations à générer aveuglément.

## 29.2 Réutilisation des données

- Une technologie doit être stockée une seule fois.
- Un média doit être réutilisable.
- Les catégories doivent être gérées de manière cohérente.
- Les informations de profil ne doivent pas être recopiées dans tous les modules.
- Les informations de SEO doivent être harmonisées.
- Les messages et demandes de contact ne doivent pas être dupliqués entre Services et Communication.

## 29.3 Relations

La conception devra identifier :

- relations un-à-un ;
- relations un-à-plusieurs ;
- relations plusieurs-à-plusieurs ;
- relations polymorphiques lorsque justifiées ;
- dépendances entre domaines ;
- tables pivots ;
- éventuelles relations facultatives.

## 29.4 Intégrité

Chaque table devra être étudiée pour définir :

- clé primaire ;
- clés étrangères ;
- champs obligatoires et facultatifs ;
- types PostgreSQL ;
- valeurs par défaut ;
- contraintes d'unicité ;
- index ;
- règles de suppression et de mise à jour ;
- gestion des dates ;
- stratégie de statut ;
- règles de conservation et d'archivage.

## 29.5 Conventions

Les conventions Laravel/Eloquent et PostgreSQL devront être respectées autant que possible : noms cohérents, clés étrangères explicites, timestamps lorsque pertinents, migrations versionnées et types adaptés.

Les tables pivots ne devront contenir que les informations nécessaires à la relation, sauf besoin métier réel de leur ajouter des attributs.

---

# 30. Inventaire initial des entités candidates

Cet inventaire sert de base à la conception détaillée. Il doit être révisé, fusionné, complété ou simplifié après étude des relations et des règles métier.

| Domaine | Entités candidates |
|---|---|
| 1. Identité et profil | `users`, `profiles`, `social_links` |
| 2. Configuration et navigation | `site_settings`, `navigation_menus`, `navigation_items` |
| 3. Services | `service_categories`, `services`, `service_features` |
| 4. Technologies | `technology_categories`, `technologies` |
| 5. Compétences | `skill_categories`, `skills` |
| 6. Portfolio | `project_categories`, `projects`, `project_features` |
| 7. Études de cas | `case_studies`, éventuellement structures de défis et solutions |
| 8. Parcours | `experiences`, `educations` |
| 9. Certifications et réalisations | `certifications`, `achievements` |
| 10. Chronologie | `timeline_events` |
| 11. Laboratoire | `experiment_categories`, `experiments` |
| 12. Blog | `posts`, `post_categories`, `tags` |
| 13. Communication | `messages`, `testimonials`, `newsletter_subscribers`, éventuellement `contact_requests`, `newsletter_campaigns` |
| 14. Médias | `media`, éventuellement `media_collections` |
| 15. CV | `resumes`, éventuellement `resume_versions` |
| 16. Administration, sécurité et SEO | `activity_logs`, `seo_metadata`, `redirects` (rôles par colonne `users.role`, décision D-04) |
| 17. Analytics | `page_views`, `events` |
| 18. Pages et sections | éventuellement `pages`, `page_sections` ou une configuration de sections |

## 30.1 Tables pivots à étudier

Les relations suivantes pourront nécessiter des tables pivots :

- services ↔ technologies ;
- projets ↔ technologies ;
- compétences ↔ technologies ;
- projets ↔ compétences ;
- expériences ↔ technologies ;
- expérimentations ↔ technologies ;
- articles ↔ tags ;
- pages ↔ contenus mis en avant, si nécessaire.

Les liaisons utilisateurs ↔ rôles et rôles ↔ permissions ne sont pas nécessaires en V1 (décision D-04).

La liste définitive dépendra des cardinalités et des besoins réels.

## 30.2 Entités supplémentaires non encore décidées

Les éléments suivants ont été évoqués comme possibilités, mais ne sont pas validés définitivement :

- `availability` : gestion de la disponibilité professionnelle ;
- `newsletter_campaigns` : gestion de campagnes de newsletter ;
- `contact_requests` : demandes de contact ou de devis distinctes ;
- `resume_versions` : historique de versions du CV ;
- `media_collections` : organisation des médias ;
- `pages` et `page_sections` : gestion éditoriale de pages et sections ;
- `challenges` et `solutions` : détails structurés d'études de cas.

Chaque élément devra être justifié avant d'être intégré au modèle final.

---

# 31. Critères globaux de réussite

WARISS.DEV pourra être considéré comme conforme à sa vision lorsque :

1. Le visiteur peut comprendre rapidement qui est le propriétaire du site et ce qu'il propose.
2. Le visiteur peut consulter les compétences, expériences, formations et réalisations.
3. Les projets sont présentés avec un niveau de détail adapté.
4. Les services sont clairement décrits et permettent de prendre contact.
5. Les articles et contenus techniques peuvent être publiés et consultés.
6. Les démonstrations du laboratoire fonctionnent sans compromettre la sécurité.
7. Le CV public autorisé peut être consulté ou téléchargé.
8. Les formulaires de communication fonctionnent et les messages sont gérables.
9. L'administrateur peut gérer les principaux contenus sans modifier le code.
10. Les menus et certains paramètres des pages sont administrables.
11. Les données sont cohérentes et réutilisées sans duplication inutile.
12. Les contenus non publiés ne sont pas exposés au public.
13. Les permissions et règles de sécurité sont appliquées côté backend.
14. Le site est responsive, performant, accessible et optimisé pour le référencement.
15. La plateforme peut évoluer sans remettre en cause toute son architecture.

---

# 32. Plan de travail après validation du cahier des charges

Le cahier des charges global sert de référence fonctionnelle. La prochaine phase est la conception complète de la base de données.

## Étape 1 — Inventaire définitif des entités

Pour chacun des 18 domaines :

- examiner les entités candidates ;
- confirmer leur utilité ;
- fusionner les entités redondantes ;
- identifier les entités manquantes ;
- distinguer entités métier, tables de configuration et tables pivots.

## Étape 2 — Définition des relations

- identifier les relations 1:1 ;
- identifier les relations 1:N ;
- identifier les relations N:N ;
- préciser les cardinalités ;
- identifier les relations facultatives ;
- définir les dépendances entre domaines.

## Étape 3 — Organisation des tables

- déterminer les tables principales ;
- déterminer les tables pivots ;
- déterminer les tables de traduction si nécessaire ;
- harmoniser les conventions ;
- éviter les duplications.

## Étape 4 — Colonnes et types

Pour chaque table, définir :

- nom de colonne ;
- type PostgreSQL ;
- nullable ou non ;
- valeur par défaut ;
- description fonctionnelle ;
- règle de validation.

## Étape 5 — Contraintes et intégrité

- clés primaires ;
- clés étrangères ;
- contraintes uniques ;
- index ;
- règles `ON DELETE` et `ON UPDATE` ;
- règles d'archivage ;
- contraintes métier.

## Étape 6 — Modélisation

Produire :

- MCD ;
- MLD ;
- diagramme relationnel ;
- dictionnaire de données.

## Étape 7 — Validation technique

Vérifier que le modèle est cohérent avec Laravel, Eloquent, PostgreSQL, l'API REST, le CMS et les besoins du frontend.

## Étape 8 — Développement

Après validation du modèle seulement :

- migrations Laravel ;
- modèles Eloquent ;
- relations ;
- factories et seeders ;
- validations ;
- services métier ;
- endpoints API ;
- tests.

Aucune migration ne doit être générée avant validation de la conception.

---

# 33. Organisation documentaire recommandée

Décision D-05 (21/11/2026) : une seule arborescence, décrite dans le `README.md` à la racine du dépôt, qui remplace les deux propositions précédentes.

```text
documentation/
├── 00-VISION/
│   └── cahier-des-charges-general.md        # le présent document
├── 01-FUNCTIONAL-SPECIFICATIONS/            # une fiche par page ou module, au besoin
├── 02-DATABASE/
│   └── structure-base-de-donnees.md
├── 03-TECHNICAL-ARCHITECTURE/
│   └── architecture-technique.md
├── 04-API-SPECIFICATIONS/                   # généré depuis OpenAPI + conventions
├── 05-DESIGN-SYSTEM/
├── 06-TESTING/
├── 07-DEPLOYMENT/
└── 99-ARCHIVE/
    └── cahier-des-charges-general_2026-11-14.md
```

Les documents détaillés des domaines pourront être ajoutés plus tard si un module nécessite une spécification complémentaire. Le présent document reste la référence générale.

---

# 34. Conclusion

WARISS.DEV est conçu comme une plateforme personnelle professionnelle dotée d'un CMS personnalisé et hybride. Le projet associe une vitrine publique riche, un portfolio structuré, des contenus éditoriaux, un laboratoire technique, des outils de communication, une administration et des fonctions de suivi.

Les 18 domaines fonctionnels constituent le périmètre de référence. Les entités et relations proposées dans ce document sont des candidates à examiner, pas un schéma SQL déjà figé.

La priorité immédiate est de transformer cette vision fonctionnelle en un modèle de données cohérent, normalisé, sécurisé et évolutif.

**Décision de méthode :** terminer la conception de la base de données avant de commencer les migrations et le développement des modules métier.

---

**Fin du cahier des charges général — WARISS.DEV**
