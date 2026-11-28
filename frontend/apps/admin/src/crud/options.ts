/**
 * Valeurs autorisées par l'API (énumérations du backend, app/Enums) et leurs libellés français.
 */
import type { Option } from './types';

const options = (entries: Record<string, string>): Option[] => Object.entries(entries).map(([value, label]) => ({ value, label }));

export const PUBLICATION_STATUS = options({ draft: 'Brouillon', scheduled: 'Programmé', published: 'Publié', archived: 'Archivé' });
export const PROJECT_KIND = options({
  professional: 'Professionnel',
  freelance: 'Freelance',
  personal: 'Personnel',
  academic: 'Académique',
  open_source: 'Open source',
});
export const PROJECT_STAGE = options({ planned: 'Prévu', in_progress: 'En cours', completed: 'Terminé', maintained: 'Maintenu', paused: 'En pause' });
export const CASE_STUDY_SECTION = options({
  context: 'Contexte',
  need: 'Besoin',
  objectives: 'Objectifs',
  constraints: 'Contraintes',
  challenge: 'Défi',
  solution: 'Solution',
  architecture: 'Architecture',
  results: 'Résultats',
  lessons: 'Enseignements',
  custom: 'Personnalisée',
});
export const PRICING_MODEL = options({ fixed: 'Forfait', hourly: 'À l’heure', monthly: 'Au mois', quote: 'Sur devis' });
export const SERVICE_FEATURE_KIND = options({ benefit: 'Bénéfice', included: 'Inclus', deliverable: 'Livrable' });
export const EXPERIMENT_KIND = options({
  demo: 'Démonstration',
  prototype: 'Prototype',
  tool: 'Outil',
  api_test: 'Test d’API',
  visualization: 'Visualisation',
  ui_experiment: 'Expérience d’interface',
  learning: 'Apprentissage',
});
export const POST_TYPE = options({
  article: 'Article',
  tutorial: 'Tutoriel',
  guide: 'Guide',
  feedback: 'Retour d’expérience',
  note: 'Note',
  tip: 'Astuce',
  news: 'Actualité',
});
export const PROFICIENCY = options({ learning: 'En apprentissage', working: 'Opérationnel', proficient: 'Confirmé', expert: 'Expert' });
export const EMPLOYMENT_TYPE = options({
  full_time: 'Temps plein',
  part_time: 'Temps partiel',
  contract: 'Contrat',
  freelance: 'Freelance',
  internship: 'Stage',
  apprenticeship: 'Alternance',
  volunteer: 'Bénévolat',
});
export const DEGREE_LEVEL = options({
  certificate: 'Certificat',
  diploma: 'Diplôme',
  bachelor: 'Licence / Bachelor',
  master: 'Master',
  doctorate: 'Doctorat',
  bootcamp: 'Bootcamp',
  self_taught: 'Autodidacte',
  other: 'Autre',
});
export const COMPLETION_STATUS = options({ in_progress: 'En cours', completed: 'Obtenu', interrupted: 'Interrompu' });
export const ACHIEVEMENT_TYPE = options({
  award: 'Prix',
  distinction: 'Distinction',
  publication: 'Publication',
  talk: 'Intervention',
  hackathon: 'Hackathon',
  open_source: 'Open source',
  milestone: 'Jalon',
  other: 'Autre',
});
export const TIMELINE_TYPE = options({
  education: 'Formation',
  experience: 'Expérience',
  certification: 'Certification',
  project: 'Projet',
  achievement: 'Réalisation',
  learning: 'Apprentissage',
  event: 'Événement',
});
export const TIMELINE_SOURCE = options({
  education: 'Formation',
  experience: 'Expérience',
  certification: 'Certification',
  project: 'Projet',
  achievement: 'Réalisation',
});
export const TESTIMONIAL_STATUS = options({ pending: 'En attente', approved: 'Approuvé', rejected: 'Refusé' });
export const MESSAGE_STATUS = options({ new: 'Nouveau', read: 'Lu', in_progress: 'En cours', replied: 'Répondu', archived: 'Archivé', spam: 'Indésirable' });
export const MESSAGE_TYPE = options({ contact: 'Contact', quote: 'Devis', collaboration: 'Collaboration' });
export const SUBSCRIBER_STATUS = options({ pending: 'À confirmer', subscribed: 'Abonné', unsubscribed: 'Désabonné', bounced: 'En échec' });
export const CAMPAIGN_STATUS = options({ draft: 'Brouillon', scheduled: 'Programmée', sending: 'En cours d’envoi', sent: 'Envoyée', failed: 'Échec' });
export const PAGE_SECTION_TYPE = options({
  hero: 'Bandeau principal',
  about: 'Présentation',
  skills: 'Compétences',
  featured_projects: 'Projets mis en avant',
  experiences: 'Parcours',
  services: 'Services',
  latest_posts: 'Derniers articles',
  cta: 'Appel à l’action',
  contact: 'Contact',
  richtext: 'Texte libre',
  gallery: 'Galerie',
  faq: 'Questions fréquentes',
});
export const NAVIGATION_ITEM_TYPE = options({ page: 'Page du site', route: 'Page fixe', external: 'Lien externe' });
export const NAVIGATION_LOCATION = options({ header: 'En-tête', footer: 'Pied de page', secondary: 'Secondaire', legal: 'Informations légales' });
export const ROUTE_KEYS = options({
  home: 'Accueil',
  about: 'À propos',
  skills: 'Compétences',
  experience: 'Parcours',
  projects: 'Projets',
  services: 'Services',
  lab: 'Laboratoire',
  blog: 'Blog',
  achievements: 'Réalisations',
  testimonials: 'Témoignages',
  resume: 'CV',
  contact: 'Contact',
  terminal: 'Terminal',
});
export const RESUME_TARGET = options({ general: 'Général', freelance: 'Freelance', employment: 'Emploi', other: 'Autre' });
export const RESUME_STATUS = options({ draft: 'Brouillon', active: 'Actif', archived: 'Archivé' });
export const LOCALE = options({ fr: 'Français', en: 'Anglais' });
export const REDIRECT_STATUS = options({ '301': '301 · définitive', '302': '302 · temporaire', '307': '307 · temporaire', '308': '308 · définitive' });
export const MEDIA_KIND = options({ image: 'Image', video: 'Vidéo', document: 'Document', icon: 'Icône', other: 'Autre' });
export const MEDIA_VISIBILITY = options({ public: 'Public', private: 'Privé' });
export const SOCIAL_PLATFORM = options({
  github: 'GitHub',
  linkedin: 'LinkedIn',
  x: 'X',
  gitlab: 'GitLab',
  youtube: 'YouTube',
  mastodon: 'Mastodon',
  bluesky: 'Bluesky',
  stackoverflow: 'Stack Overflow',
  codepen: 'CodePen',
  email: 'E-mail',
  rss: 'RSS',
  website: 'Site web',
});
export const ACTIVITY_EVENT = options({
  login: 'Connexion',
  logout: 'Déconnexion',
  created: 'Création',
  updated: 'Modification',
  published: 'Publication',
  unpublished: 'Dépublication',
  deleted: 'Suppression',
  settings_changed: 'Réglages',
  password_reset: 'Mot de passe',
});

/** Libellé d'une valeur d'énumération. */
export function optionLabel(list: Option[] | undefined, value: unknown): string {
  return list?.find((option) => option.value === String(value))?.label ?? String(value ?? '—');
}
