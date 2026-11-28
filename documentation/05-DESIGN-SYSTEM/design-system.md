# Design system WARISS.DEV

> Décision D-20, 28/11/2026. Ce document précise la direction « Premium Tech » de l'architecture (§4.1 et §4.5). Il fait référence pour `frontend/packages/ui` : tout composant en découle, et aucune couleur ni taille n'est écrite en dur dans une application.

## 1. Intention

Un site de **développeur senior** : sobre, précis, rapide, avec quelques effets qui marquent sans distraire. Le recruteur doit trouver en 10 secondes qui je suis, ce que je sais faire et comment me contacter. Le développeur curieux trouve la profondeur : laboratoire, terminal et études de cas.

Principes :

1. **Le contenu d'abord**. Les effets servent la hiérarchie et ne décorent pas. Une page reste lisible si les animations sont désactivées.
2. **Une seule couleur d'action** (le bleu) et un accent de mise en valeur (le turquoise). Tout le reste est neutre.
3. **Dense, mais aéré**. Une grille stricte, des espacements réguliers sur une base de 4 px, des titres courts.
4. **Accessible par construction**. Contraste ≥ 4,5:1 pour le texte (vérifié, §3.3), focus toujours visible, navigation complète au clavier, `prefers-reduced-motion` respecté.
5. **Rapide**. Les effets sont faits en CSS (dégradés, `backdrop-filter`, variables), sans canvas ni WebGL. JavaScript n'intervient que pour les interactions.

## 2. Éléments signature

| Élément | Où | Description |
| --- | --- | --- |
| Fond aurore | site public, sections hero | Deux dégradés radiaux (bleu et turquoise) très flous qui dérivent lentement (40 s), une grille fine masquée en fondu et un léger grain. Les animations sont figées si l'utilisateur réduit les mouvements. |
| Cartes « projecteur » | cartes de projets, services et articles | Un halo radial suit le pointeur (variables `--pointer-x` et `--pointer-y`) et la bordure s'éclaire sous le curseur. |
| Texte dégradé | grands titres | Dégradé bleu vers turquoise sur un mot clé du titre, jamais sur un paragraphe. |
| Palette de commandes | partout (`⌘K` / `Ctrl K`) | Navigation, recherche plein texte de l'API, changement de langue et de thème, téléchargement du CV. |
| Barre de navigation en verre | en-tête | Fond translucide (`backdrop-filter: blur`), bordure qui apparaît au défilement, indicateur animé de la page active. |
| Grille « bento » | accueil | Blocs de tailles variées : présentation, disponibilité, technologies, projet phare, derniers articles, contact. |
| Bandeau de technologies | accueil | Défilement horizontal continu, mis en pause au survol et arrêté si l'utilisateur réduit les mouvements. |
| Apparition au défilement | sections | Fondu et déplacement de 12 px, une seule fois, en décalé dans les grilles (bibliothèque `motion`). |
| Progression de lecture | articles, études de cas | Fine barre en haut de page, avec un sommaire latéral qui suit la lecture. |
| Terminal | page `/terminal` et accueil (lot 3) | Simulation dans le navigateur : police mono, curseur clignotant, historique. |

## 3. Couleurs

### 3.1 Primitives (`tokens.css`, jamais utilisées directement dans un composant)

| Jeton | Valeur | Usage |
| --- | --- | --- |
| `--ink-950` | `#070B16` | fond sombre (nuit) |
| `--ink-900` | `#0B1120` | fond sombre alterné |
| `--ink-850` | `#0E1424` | surface sombre |
| `--ink-800` | `#151C2F` | surface sombre surélevée |
| `--ink-700` | `#1F2940` | bordure sombre |
| `--ink-600` | `#2C3854` | bordure sombre forte |
| `--slate-400` | `#94A3B8` | texte secondaire sombre |
| `--slate-450` | `#7C8BA1` | texte discret sombre |
| `--slate-600` | `#475569` | texte secondaire clair |
| `--slate-500` | `#64748B` | texte discret clair |
| `--cloud-50` | `#F5F7FB` | fond clair |
| `--cloud-100` | `#EDF1F7` | fond clair alterné |
| `--cloud-200` | `#E2E8F0` | bordure claire |
| `--snow` | `#FFFFFF` | surface claire |
| `--blue-400` | `#7EA6FF` | accent sombre au survol |
| `--blue-500` | `#5B8CFF` | **accent** |
| `--blue-700` | `#2B5BD9` | accent sur fond clair, bouton plein |
| `--teal-400` | `#35D6C5` | mise en valeur sombre |
| `--teal-800` | `#0B7D72` | mise en valeur claire |
| `--red-400` / `--red-700` | `#F87171` / `#C2283B` | erreur sombre / claire |
| `--green-400` / `--green-700` | `#34D399` / `#0B7A4B` | succès |
| `--amber-400` / `--amber-700` | `#FBBF24` / `#9A5B00` | avertissement |

### 3.2 Variables sémantiques (`themes.css`, seules utilisées par les composants)

`--color-bg`, `--color-bg-alt`, `--color-surface`, `--color-surface-raised`, `--color-overlay`, `--color-border`, `--color-border-strong`, `--color-text`, `--color-text-muted`, `--color-text-subtle`, `--color-accent`, `--color-accent-hover`, `--color-accent-text`, `--color-on-accent`, `--color-highlight`, `--color-danger`, `--color-success`, `--color-warning`, `--color-focus`, `--gradient-brand`, `--glow-accent`.

Deux thèmes, posés par `data-theme` sur `<html>` : **sombre** (par défaut sur le site) et **clair** (par défaut dans l'administration). Le choix du visiteur est stocké dans un cookie `theme`, lu côté serveur : la page arrive déjà dans le bon thème, sans flash.

### 3.3 Contrastes vérifiés (WCAG 2.2 AA, texte ≥ 4,5:1)

| Couple | Ratio |
| --- | --- |
| Texte sombre `#E6EAF2` sur `#070B16` | 16,3 |
| Secondaire `#94A3B8` sur fond et surface sombres | 7,7 et 7,2 |
| Discret `#7C8BA1` sur fond et surface sombres | 5,7 et 5,3 |
| Accent `#5B8CFF` sur fond sombre | 6,2 |
| Turquoise `#35D6C5` sur fond sombre | 10,8 |
| Bouton plein sombre : texte `#070B16` sur `#5B8CFF` | 6,2 |
| Bouton plein clair : blanc sur `#2B5BD9` | 5,8 |
| Texte clair `#0F172A` sur `#F5F7FB` | 16,7 |
| Secondaire `#475569` sur blanc | 7,6 |
| Discret `#64748B` sur blanc | 4,8 |
| Accent clair `#2B5BD9` sur blanc et sur `#F5F7FB` | 5,8 et 5,4 |
| Turquoise clair `#0B7D72` sur blanc | 5,0 |
| Erreur, succès et avertissement, en sombre et en clair | ≥ 5,4 |

Couples **interdits pour du texte** : blanc sur `#5B8CFF` (3,2), `#64748B` sur fond sombre (4,1), turquoise `#35D6C5` sur fond clair.

## 4. Typographie

Polices auto-hébergées avec Fontsource, en versions variables :

- **Space Grotesk** pour les titres et les chiffres clés ;
- **Inter** pour le texte et l'interface ;
- **JetBrains Mono** pour le code, le terminal, les étiquettes techniques et les métadonnées.

Échelle fluide (`clamp`), ratio d'environ 1,25 :

| Jeton | Mobile → bureau | Usage |
| --- | --- | --- |
| `--text-xs` | 12 px | étiquettes, métadonnées |
| `--text-sm` | 14 px | interface, légendes |
| `--text-md` | 16 px | texte courant |
| `--text-lg` | 18 → 20 px | chapeau, introduction |
| `--text-xl` | 20 → 24 px | titre de carte |
| `--text-2xl` | 24 → 32 px | titre de section secondaire |
| `--text-3xl` | 30 → 44 px | titre de section |
| `--text-4xl` | 38 → 64 px | titre de page |
| `--text-5xl` | 44 → 88 px | titre de l'accueil |

- Interlignage : 1,1 pour les titres d'affichage, 1,3 pour les titres, 1,65 pour le texte.
- Mesure du texte : 68 caractères au maximum. Les titres d'affichage ont un approche de lettres négatif (`-0.03em`).
- Les petites étiquettes en mono sont en majuscules, avec un approche de `0.08em`.

## 5. Espace, formes, profondeur

- **Espacement** : échelle `--space-1` à `--space-32` sur une base de 4 px (4, 8, 12, 16, 20, 24, 32, 40, 48, 64, 80, 96, 128). L'espace entre sections est fluide : `--section-y` va de 64 à 128 px.
- **Conteneur** : 1200 px pour le contenu, 1360 px en large et 720 px pour la lecture. La gouttière est de 16 px sur mobile et de 32 px sur bureau.
- **Seuils** (`min-width`, mobile first) : 640, 768, 1024 et 1280 px.
- **Rayons** : 6, 10, 14, 20 et 28 px, plus le cercle complet. Cartes à 14 px, boutons à 10 px, pastilles en cercle.
- **Ombres** : en thème sombre, la profondeur vient surtout des bordures et de la lumière (`--glow-accent`) ; en thème clair, d'ombres douces et superposées.
- **Profondeur (`z-index`)** : en-tête 100, menu déroulant 200, fenêtre modale 300, notification 400, palette de commandes 500.

## 6. Mouvement

- Durées : 120 ms (micro), 200 ms (état), 320 ms (entrée), 600 ms (apparition au défilement). Courbe `cubic-bezier(0.2, 0.8, 0.2, 1)`.
- Seuls `transform` et `opacity` sont animés, jamais la mise en page.
- `prefers-reduced-motion: reduce` supprime les déplacements, la dérive de l'aurore et le bandeau défilant ; les fondus courts restent.

## 7. Composants (`packages/ui`)

Les primitives accessibles viennent de **Radix UI** et sont habillées en CSS Modules. Chaque composant n'utilise que les variables sémantiques.

| Famille | Composants |
| --- | --- |
| Actions | `Button` (primary, secondary, ghost, danger ; tailles sm, md, lg ; icône ; chargement), `IconButton`, `ButtonLink` |
| Formulaires | `Field` (libellé, aide, erreur reliés par `aria-describedby`), `Input`, `Textarea`, `Select`, `Checkbox`, `Switch`, `RadioGroup` |
| Affichage | `Badge`, `Tag`, `Card`, `SpotlightCard`, `Avatar`, `Kbd`, `Skeleton`, `Separator`, `GradientText`, `SectionHeading`, `EmptyState` |
| Superpositions | `Dialog`, `Sheet` (panneau latéral mobile), `DropdownMenu`, `Tooltip`, `Popover`, `Toast` |
| Navigation | `Tabs`, `Pagination`, `Breadcrumbs`, `SkipLink` |
| Mise en page | `Container`, `Section`, `Stack`, `Grid`, `AuroraBackground`, `VisuallyHidden` |

Un composant est prêt quand il a :

- ses états (repos, survol, focus visible, actif, désactivé, chargement, erreur) ;
- les deux thèmes ;
- une navigation au clavier complète ;
- des noms accessibles ;
- un test Vitest et Testing Library pour son comportement.

## 8. Ton et contenu de l'interface

- Le texte d'interface est en **français** par défaut, et en anglais. Il est écrit à la première personne pour le propriétaire (« Mes projets »), en phrases courtes et sans jargon marketing.
- Les messages d'erreur disent quoi faire (« Vérifiez l'adresse e-mail »), jamais seulement « Erreur ».
- Les dates sont formatées selon la langue (`Intl`) et les nombres avec les séparateurs de la langue.
