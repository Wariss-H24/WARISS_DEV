/**
 * Formes des réponses de l'API publique (/api/v1/public).
 *
 * Exception documentée à D-10 (décision D-20) : ces réponses sont produites par le présentateur
 * générique du backend (App\Http\Resources\Public\PublicPresenter), que Scramble ne sait pas typer.
 * Elles sont donc déclarées ici, une seule fois, en miroir du présentateur et de la structure
 * de la base (documentation/02-DATABASE). Toute évolution du présentateur se reporte ici.
 *
 * Règles du présentateur : aucun champ interne (statut, dates techniques, position, visibilité) ;
 * chaque colonne <rôle>_media_id devient <rôle> (Media | null, null si le média est privé) ;
 * les champs traduits sont ceux de la langue servie (`locale`), avec `slugs` par langue.
 */

export type Locale = 'fr' | 'en';

export interface Media {
  id: number;
  kind: 'image' | 'video' | 'document' | 'icon' | 'other';
  url: string;
  mime_type: string | null;
  width: number | null;
  height: number | null;
  duration_seconds: number | null;
  alt: string | null;
  caption: string | null;
  credit: string | null;
}

/** Champs ajoutés à tout contenu traduit. */
export interface Translated {
  /** Langue réellement servie (repli sur le français si la traduction manque). */
  locale: Locale;
}

/** Contenu publié sous une URL : slug de chaque langue (sélecteur de langue, hreflang). */
export interface Slugged extends Translated {
  slug: string;
  slugs: Partial<Record<Locale, string | null>>;
}

export interface Paginated<T> {
  data: T[];
  meta: { current_page: number; last_page: number; per_page: number; total: number };
}

export interface Category extends Slugged {
  id: number;
  name: string;
  description?: string | null;
  icon?: string | null;
}

export interface Tag extends Slugged {
  id: number;
  name: string;
}

export interface Technology extends Translated {
  id: number;
  technology_category_id: number;
  name: string;
  slug: string;
  icon: Media | null;
  icon_key: string | null;
  brand_color: string | null;
  official_url: string | null;
  proficiency: 'learning' | 'working' | 'proficient' | 'expert' | null;
  is_featured: boolean;
  description: string | null;
}

export interface TechnologyCategory extends Slugged {
  id: number;
  name: string;
  technologies: Technology[];
}

export interface Skill extends Translated {
  id: number;
  skill_category_id: number;
  slug: string;
  proficiency: number | null;
  years_of_experience: string | null;
  is_featured: boolean;
  name: string;
  description: string | null;
  technologies?: Technology[];
}

export interface SkillCategory extends Slugged {
  id: number;
  name: string;
  skills: Skill[];
}

export interface Seo extends Translated {
  id: number;
  seoable_type: string;
  seoable_id: number;
  robots_index: boolean;
  robots_follow: boolean;
  canonical_url: string | null;
  og_image: Media | null;
  schema_type: string | null;
  structured_data: Record<string, unknown> | null;
  meta_title: string | null;
  meta_description: string | null;
  og_title: string | null;
  og_description: string | null;
}

interface Editorial extends Slugged {
  id: number;
  title: string;
  published_at: string;
  is_featured: boolean;
  cover: Media | null;
  /** Fiche SEO, présente dans le détail. */
  seo?: Seo | null;
  /** Vrai dans un aperçu de brouillon. */
  is_preview?: boolean;
  gallery?: Media[];
}

export interface ProjectFeature extends Translated {
  id: number;
  title: string;
  description: string | null;
}

export interface Project extends Editorial {
  project_category_id: number;
  kind: 'professional' | 'freelance' | 'personal' | 'academic' | 'open_source';
  development_stage: 'planned' | 'in_progress' | 'completed' | 'maintained' | 'paused';
  /** Masqué (null) si le projet est confidentiel. */
  client_name: string | null;
  started_on: string | null;
  ended_on: string | null;
  live_url: string | null;
  repository_url: string | null;
  demo_url: string | null;
  is_confidential: boolean;
  summary: string;
  description: string;
  context: string | null;
  role: string;
  responsibilities: string | null;
  category?: Category;
  technologies?: Technology[];
  features?: ProjectFeature[];
  skills?: Skill[];
  case_studies?: CaseStudy[];
}

export interface CaseStudySection extends Translated {
  id: number;
  type: 'context' | 'need' | 'objectives' | 'constraints' | 'challenge' | 'solution' | 'architecture' | 'results' | 'lessons' | 'custom';
  title: string | null;
  body: string;
}

export interface CaseStudy extends Editorial {
  project_id: number | null;
  started_on: string | null;
  ended_on: string | null;
  reading_time_minutes: number | null;
  summary: string;
  project?: Project | null;
  technologies?: Technology[];
  sections?: CaseStudySection[];
}

export interface ServiceFeature extends Translated {
  id: number;
  kind: 'benefit' | 'included' | 'deliverable';
  icon: string | null;
  label: string;
  description: string | null;
}

export interface Service extends Editorial {
  service_category_id: number;
  icon: string | null;
  pricing_model: 'fixed' | 'hourly' | 'monthly' | 'quote' | null;
  price_from: string | null;
  price_currency: string | null;
  delivery_min_days: number | null;
  delivery_max_days: number | null;
  summary: string;
  description: string;
  target_audience: string | null;
  cta_label: string | null;
  category?: Category;
  technologies?: Technology[];
  features?: ServiceFeature[];
}

export interface Experiment extends Editorial {
  experiment_category_id: number;
  kind: 'demo' | 'prototype' | 'tool' | 'api_test' | 'visualization' | 'ui_experiment' | 'learning';
  demo_url: string | null;
  repository_url: string | null;
  video_url: string | null;
  demo_component_key: string | null;
  experimented_on: string | null;
  summary: string;
  description: string;
  objective: string | null;
  technical_notes: string | null;
  category?: Category;
  technologies?: Technology[];
}

export interface Post extends Editorial {
  post_category_id: number;
  type: 'article' | 'tutorial' | 'guide' | 'feedback' | 'note' | 'tip' | 'news';
  reading_time_minutes: number | null;
  excerpt: string;
  /** Markdown, à convertir et nettoyer à l'affichage. */
  content: string;
  category?: Category;
  tags?: Tag[];
}

export type PageSectionType =
  'hero' | 'about' | 'skills' | 'featured_projects' | 'experiences' | 'services' | 'latest_posts' | 'cta' | 'contact' | 'richtext' | 'gallery' | 'faq';

export interface PageSectionItem {
  id: number;
  page_section_id: number;
  itemable_type: 'project' | 'service' | 'post' | 'skill' | 'certification' | string;
  itemable_id: number;
  itemable: Record<string, unknown> & { id: number };
}

export interface PageSection extends Translated {
  id: number;
  page_id: number;
  type: PageSectionType;
  config: Record<string, unknown>;
  title: string | null;
  subtitle: string | null;
  body: string | null;
  cta_label: string | null;
  cta_url: string | null;
  items: PageSectionItem[];
}

export interface Page extends Translated {
  id: number;
  key: string | null;
  kind: 'system' | 'custom';
  template: string | null;
  published_at: string;
  title: string;
  slug: string | null;
  slugs: Partial<Record<Locale, string | null>>;
  summary: string | null;
  sections: PageSection[];
  seo: Seo | null;
  is_preview?: boolean;
}

export interface SocialLink {
  id: number;
  profile_id: number;
  platform: string;
  url: string;
}

export interface Profile extends Translated {
  id: number;
  first_name: string;
  last_name: string;
  display_name: string;
  public_email: string;
  public_phone: string | null;
  country_code: string | null;
  avatar: Media | null;
  cover: Media | null;
  availability_status: 'available' | 'limited' | 'unavailable';
  available_from: string | null;
  spoken_languages: Array<{ code: string; level: string }>;
  headline: string;
  short_bio: string;
  biography: string;
  location_label: string | null;
  availability_note: string | null;
  social_links: SocialLink[];
}

export interface MenuItem {
  label: string | null;
  type: 'page' | 'route' | 'external';
  route_key: string | null;
  url: string | null;
  page: { key: string | null; slug: string | null } | null;
  icon: string | null;
  opens_in_new_tab: boolean;
  children: MenuItem[];
}

export interface Menu {
  name: string;
  slug: string;
  items: MenuItem[];
}

export interface Site {
  locale: Locale;
  locales: Locale[];
  /** Réglages publics par groupe puis par clé (un réglage « media » vaut Media | null). */
  settings: Record<string, Record<string, unknown>>;
  profile: Profile | null;
  /** Menus actifs par emplacement (header, footer, legal…). */
  menus: Partial<Record<'header' | 'footer' | 'secondary' | 'legal', Menu>> & Record<string, Menu | undefined>;
}

export interface Experience extends Translated {
  id: number;
  organization_name: string;
  organization_url: string | null;
  organization_logo: Media | null;
  employment_type: 'full_time' | 'part_time' | 'contract' | 'freelance' | 'internship' | 'apprenticeship' | 'volunteer';
  is_remote: boolean;
  country_code: string | null;
  started_on: string;
  ended_on: string | null;
  is_current: boolean;
  job_title: string;
  location_label: string | null;
  summary: string;
  description: string;
  achievements: string | null;
  technologies?: Technology[];
}

export interface Education extends Translated {
  id: number;
  institution_name: string;
  institution_url: string | null;
  institution_logo: Media | null;
  degree_level: string;
  completion_status: 'in_progress' | 'completed' | 'interrupted';
  started_on: string | null;
  ended_on: string | null;
  country_code: string | null;
  degree_title: string;
  field_of_study: string | null;
  location_label: string | null;
  description: string | null;
}

export interface Certification extends Translated {
  id: number;
  issuer_name: string;
  issuer_url: string | null;
  credential_id: string | null;
  verification_url: string | null;
  issued_on: string;
  expires_on: string | null;
  certificate: Media | null;
  is_featured: boolean;
  title: string;
  description: string | null;
  technologies?: Technology[];
}

export interface Achievement extends Translated {
  id: number;
  type: string;
  achieved_on: string;
  organization_name: string | null;
  url: string | null;
  media: Media | null;
  is_featured: boolean;
  title: string;
  description: string | null;
}

export interface TimelineEvent extends Translated {
  id: number;
  type: 'education' | 'experience' | 'certification' | 'project' | 'achievement' | 'learning' | 'event';
  event_date: string;
  end_date: string | null;
  eventable_type: string | null;
  eventable_id: number | null;
  image: Media | null;
  title: string | null;
  description: string | null;
  eventable?: (Record<string, unknown> & { id: number }) | null;
}

export interface Testimonial extends Translated {
  id: number;
  author_name: string;
  author_company: string | null;
  photo: Media | null;
  project_id: number | null;
  given_on: string | null;
  is_featured: boolean;
  quote: string;
  author_role: string | null;
  project?: Project | null;
}

export interface ResumeInfo {
  title: string;
  target: 'general' | 'freelance' | 'employment' | 'other';
  locale: Locale;
  is_default: boolean;
  version_number: number | null;
  size_bytes: number | null;
  updated_at: string | null;
}

export interface SearchResults {
  projects?: Project[];
  services?: Service[];
  experiments?: Experiment[];
  posts?: Post[];
}

export type ShareNetwork = 'linkedin' | 'x' | 'facebook' | 'whatsapp' | 'email' | 'copy_link';
export type PublicContentType = 'page' | 'project' | 'case_study' | 'service' | 'experiment' | 'post';
