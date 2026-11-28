/**
 * Catalogue des contenus de l'administration (cahier des charges §23), en miroir des FormRequest
 * du backend (backend/app/Http/Requests/Admin). Une entrée = une liste + un formulaire complets.
 */
import {
  Award,
  BadgeCheck,
  Blocks,
  Briefcase,
  Folder,
  FolderKanban,
  FileText,
  FlaskConical,
  GraduationCap,
  History,
  Layers,
  LayoutTemplate,
  Link2,
  ListTree,
  Mail,
  Megaphone,
  Menu,
  MessageSquareQuote,
  Milestone,
  Newspaper,
  Route,
  Sparkles,
  Tags,
  Trophy,
  Wrench,
} from 'lucide-react';
import { SITE_URL } from '~/lib/http';
import * as O from './options';
import type { ColumnDef, FieldDef, FieldGroup, ResourceConfig } from './types';

/* ------------------------------------------------------------------ Blocs réutilisables */

const editorial = (title = 'Publication'): FieldGroup => ({
  title,
  description: 'Un contenu programmé est publié automatiquement à la date choisie.',
  fields: [
    { name: 'status', label: 'Statut', type: 'select', options: O.PUBLICATION_STATUS, defaultValue: 'draft' },
    {
      name: 'published_at',
      label: 'Date de publication',
      type: 'datetime',
      visibleIf: (values) => values.status === 'scheduled' || values.status === 'published',
    },
    { name: 'is_featured', label: 'Mis en avant', type: 'boolean' },
    { name: 'position', label: 'Ordre d’affichage', type: 'number', defaultValue: 0 },
  ],
});

const display = (featurable = true): FieldDef[] => [
  { name: 'is_visible', label: 'Visible sur le site', type: 'boolean', defaultValue: true },
  ...(featurable ? [{ name: 'is_featured', label: 'Mis en avant', type: 'boolean' as const }] : []),
  { name: 'position', label: 'Ordre d’affichage', type: 'number', defaultValue: 0 },
];

const titleSlug = (label = 'Titre'): FieldDef[] => [
  { name: 'title', label, type: 'text', translatable: true, required: true, wide: true },
  {
    name: 'slug',
    label: 'Adresse (slug)',
    type: 'slug',
    translatable: true,
    required: true,
    slugFrom: 'title',
    hint: 'Partie de l’adresse : lettres minuscules, chiffres et tirets.',
  },
];

const statusColumns: ColumnDef[] = [
  { key: 'status', label: 'Statut', format: 'status', options: O.PUBLICATION_STATUS, sortable: false },
  { key: 'published_at', label: 'Publication', format: 'date', sortable: true },
];

const publicSlug = (segment: string) => (item: Record<string, unknown>) => {
  const slug = (item.translations as Record<string, { slug?: string }> | undefined)?.fr?.slug;

  return slug ? `${SITE_URL}/fr/${segment}/${slug}` : null;
};

/** Catégorie traduite (nom + slug), commune à toutes les taxonomies. */
function category(key: string, label: string, group: string, extra: FieldDef[] = []): ResourceConfig {
  return {
    key,
    label,
    singular: 'catégorie',
    feminine: true,
    icon: Folder,
    group,
    groups: [
      {
        title: 'Catégorie',
        fields: [
          { name: 'name', label: 'Nom', type: 'text', translatable: true, required: true },
          { name: 'slug', label: 'Adresse (slug)', type: 'slug', translatable: true, required: true, slugFrom: 'name' },
          ...extra,
          { name: 'is_active', label: 'Active', type: 'boolean', defaultValue: true },
          { name: 'position', label: 'Ordre d’affichage', type: 'number', defaultValue: 0 },
        ],
      },
    ],
    columns: [
      { key: 'name', label: 'Nom', path: 'translations.fr.name', format: 'label' },
      { key: 'is_active', label: 'Active', format: 'boolean' },
      { key: 'position', label: 'Ordre', sortable: true },
    ],
    defaultSort: 'position',
  };
}

/* ------------------------------------------------------------------ Contenus */

export const RESOURCES: ResourceConfig[] = [
  /* Portfolio */
  {
    key: 'projects',
    label: 'Projets',
    singular: 'projet',
    icon: FolderKanban,
    group: 'Portfolio',
    contentType: 'project',
    softDeletes: true,
    publicPath: publicSlug('projects'),
    groups: [
      {
        title: 'Présentation',
        fields: [
          ...titleSlug(),
          {
            name: 'summary',
            label: 'Résumé',
            type: 'textarea',
            translatable: true,
            required: true,
            wide: true,
            hint: 'Affiché sur les cartes : 1 à 2 phrases.',
          },
          { name: 'description', label: 'Description', type: 'markdown', translatable: true, required: true, wide: true },
          { name: 'context', label: 'Contexte', type: 'markdown', translatable: true, wide: true },
          { name: 'role', label: 'Mon rôle', type: 'text', translatable: true, required: true, wide: true },
          { name: 'responsibilities', label: 'Responsabilités', type: 'markdown', translatable: true, wide: true },
        ],
      },
      {
        title: 'Caractéristiques',
        fields: [
          { name: 'project_category_id', label: 'Catégorie', type: 'relation', resource: 'project-categories', required: true },
          { name: 'kind', label: 'Type', type: 'select', options: O.PROJECT_KIND, required: true },
          { name: 'development_stage', label: 'État', type: 'select', options: O.PROJECT_STAGE, required: true },
          { name: 'client_name', label: 'Client', type: 'text' },
          { name: 'started_on', label: 'Début', type: 'date' },
          { name: 'ended_on', label: 'Fin', type: 'date' },
          { name: 'is_confidential', label: 'Confidentiel (masque client et liens)', type: 'boolean' },
          { name: 'cover_media_id', label: 'Image de couverture', type: 'media' },
        ],
      },
      {
        title: 'Liens',
        fields: [
          { name: 'live_url', label: 'Site en ligne', type: 'url' },
          { name: 'demo_url', label: 'Démonstration', type: 'url' },
          { name: 'repository_url', label: 'Code source', type: 'url' },
        ],
      },
      {
        title: 'Technologies et compétences',
        fields: [
          { name: 'technology_ids', label: 'Technologies', type: 'relations', resource: 'technologies', wide: true },
          { name: 'skill_ids', label: 'Compétences', type: 'relations', resource: 'skills', wide: true },
        ],
      },
      {
        title: 'Fonctionnalités clés',
        fields: [
          {
            name: 'features',
            label: 'Fonctionnalités',
            type: 'children',
            wide: true,
            fields: [
              { name: 'title', label: 'Titre', type: 'text', translatable: true, required: true },
              { name: 'description', label: 'Description', type: 'textarea', translatable: true },
              { name: 'is_visible', label: 'Visible', type: 'boolean', defaultValue: true },
            ],
          },
        ],
      },
      editorial(),
    ],
    columns: [
      { key: 'title', label: 'Titre', path: 'translations.fr.title', format: 'label' },
      { key: 'kind', label: 'Type', format: 'badge', options: O.PROJECT_KIND },
      ...statusColumns,
    ],
    filters: [
      { name: 'status', label: 'Statut', options: O.PUBLICATION_STATUS },
      { name: 'kind', label: 'Type', options: O.PROJECT_KIND },
    ],
    defaultSort: '-id',
  },
  {
    key: 'case-studies',
    label: 'Études de cas',
    singular: 'étude de cas',
    feminine: true,
    icon: FileText,
    group: 'Portfolio',
    contentType: 'case_study',
    softDeletes: true,
    publicPath: publicSlug('case-studies'),
    groups: [
      {
        title: 'Présentation',
        fields: [
          ...titleSlug(),
          { name: 'summary', label: 'Résumé', type: 'textarea', translatable: true, required: true, wide: true },
          { name: 'project_id', label: 'Projet lié', type: 'relation', resource: 'projects' },
          { name: 'reading_time_minutes', label: 'Temps de lecture (min)', type: 'number' },
          { name: 'started_on', label: 'Début', type: 'date' },
          { name: 'ended_on', label: 'Fin', type: 'date' },
          { name: 'cover_media_id', label: 'Image de couverture', type: 'media' },
          { name: 'technology_ids', label: 'Technologies', type: 'relations', resource: 'technologies', wide: true },
        ],
      },
      {
        title: 'Sections',
        description: 'Contexte, besoin, solution, résultats… dans l’ordre de lecture.',
        fields: [
          {
            name: 'sections',
            label: 'Sections',
            type: 'children',
            wide: true,
            fields: [
              { name: 'type', label: 'Type', type: 'select', options: O.CASE_STUDY_SECTION, required: true, defaultValue: 'context' },
              { name: 'title', label: 'Titre', type: 'text', translatable: true },
              { name: 'body', label: 'Contenu', type: 'markdown', translatable: true, required: true },
            ],
          },
        ],
      },
      editorial(),
    ],
    columns: [{ key: 'title', label: 'Titre', path: 'translations.fr.title', format: 'label' }, ...statusColumns],
    filters: [{ name: 'status', label: 'Statut', options: O.PUBLICATION_STATUS }],
  },
  category('project-categories', 'Catégories de projets', 'Portfolio'),

  /* Services */
  {
    key: 'services',
    label: 'Services',
    singular: 'service',
    icon: Briefcase,
    group: 'Services',
    contentType: 'service',
    softDeletes: true,
    publicPath: publicSlug('services'),
    groups: [
      {
        title: 'Présentation',
        fields: [
          ...titleSlug(),
          { name: 'summary', label: 'Résumé', type: 'textarea', translatable: true, required: true, wide: true },
          { name: 'description', label: 'Description', type: 'markdown', translatable: true, required: true, wide: true },
          { name: 'target_audience', label: 'Pour qui', type: 'textarea', translatable: true, wide: true },
          { name: 'cta_label', label: 'Texte du bouton', type: 'text', translatable: true, placeholder: 'Demander un devis' },
        ],
      },
      {
        title: 'Offre',
        fields: [
          { name: 'service_category_id', label: 'Catégorie', type: 'relation', resource: 'service-categories', required: true },
          { name: 'pricing_model', label: 'Tarification', type: 'select', options: O.PRICING_MODEL },
          { name: 'price_from', label: 'Prix à partir de', type: 'number', visibleIf: (values) => values.pricing_model !== 'quote' },
          { name: 'price_currency', label: 'Devise (EUR, XOF…)', type: 'text', placeholder: 'EUR', visibleIf: (values) => values.pricing_model !== 'quote' },
          { name: 'delivery_min_days', label: 'Délai minimum (jours)', type: 'number' },
          { name: 'delivery_max_days', label: 'Délai maximum (jours)', type: 'number' },
          { name: 'icon', label: 'Icône (clé)', type: 'text' },
          { name: 'cover_media_id', label: 'Image de couverture', type: 'media' },
          { name: 'technology_ids', label: 'Technologies', type: 'relations', resource: 'technologies', wide: true },
        ],
      },
      {
        title: 'Contenu de l’offre',
        fields: [
          {
            name: 'features',
            label: 'Éléments',
            type: 'children',
            wide: true,
            fields: [
              { name: 'kind', label: 'Nature', type: 'select', options: O.SERVICE_FEATURE_KIND, required: true, defaultValue: 'included' },
              { name: 'label', label: 'Libellé', type: 'text', translatable: true, required: true },
              { name: 'description', label: 'Description', type: 'textarea', translatable: true },
              { name: 'icon', label: 'Icône', type: 'text' },
              { name: 'is_visible', label: 'Visible', type: 'boolean', defaultValue: true },
            ],
          },
        ],
      },
      editorial(),
    ],
    columns: [
      { key: 'title', label: 'Titre', path: 'translations.fr.title', format: 'label' },
      { key: 'pricing_model', label: 'Tarification', format: 'badge', options: O.PRICING_MODEL },
      ...statusColumns,
    ],
    filters: [{ name: 'status', label: 'Statut', options: O.PUBLICATION_STATUS }],
  },
  category('service-categories', 'Catégories de services', 'Services', [
    { name: 'description', label: 'Description', type: 'textarea', translatable: true, wide: true },
    { name: 'icon', label: 'Icône (clé)', type: 'text' },
  ]),

  /* Catalogue */
  {
    key: 'technologies',
    label: 'Technologies',
    singular: 'technologie',
    feminine: true,
    icon: Wrench,
    group: 'Catalogue',
    groups: [
      {
        title: 'Technologie',
        fields: [
          { name: 'name', label: 'Nom', type: 'text', required: true },
          { name: 'slug', label: 'Identifiant (slug)', type: 'slug', required: true, slugFrom: 'name' },
          { name: 'technology_category_id', label: 'Catégorie', type: 'relation', resource: 'technology-categories', required: true },
          { name: 'proficiency', label: 'Niveau', type: 'select', options: O.PROFICIENCY },
          { name: 'brand_color', label: 'Couleur de marque', type: 'color' },
          { name: 'official_url', label: 'Site officiel', type: 'url' },
          { name: 'icon_media_id', label: 'Logo', type: 'media', mediaKind: 'image' },
          { name: 'icon_key', label: 'Icône (clé)', type: 'text' },
          { name: 'description', label: 'Description', type: 'textarea', translatable: true, wide: true },
          ...display(),
        ],
      },
    ],
    columns: [
      { key: 'name', label: 'Nom', format: 'label', sortable: true },
      { key: 'proficiency', label: 'Niveau', format: 'badge', options: O.PROFICIENCY },
      { key: 'is_visible', label: 'Visible', format: 'boolean' },
      { key: 'position', label: 'Ordre', sortable: true },
    ],
    defaultSort: 'position',
  },
  category('technology-categories', 'Catégories de technologies', 'Catalogue'),
  {
    key: 'skills',
    label: 'Compétences',
    singular: 'compétence',
    feminine: true,
    icon: Sparkles,
    group: 'Catalogue',
    groups: [
      {
        title: 'Compétence',
        fields: [
          { name: 'name', label: 'Nom', type: 'text', translatable: true, required: true },
          { name: 'slug', label: 'Identifiant (slug)', type: 'slug', required: true, slugFrom: 'name' },
          { name: 'skill_category_id', label: 'Catégorie', type: 'relation', resource: 'skill-categories', required: true },
          { name: 'proficiency', label: 'Niveau (1 à 5)', type: 'number' },
          { name: 'years_of_experience', label: 'Années d’expérience', type: 'number' },
          { name: 'description', label: 'Description', type: 'textarea', translatable: true, wide: true },
          { name: 'technology_ids', label: 'Technologies associées', type: 'relations', resource: 'technologies', wide: true },
          ...display(),
        ],
      },
    ],
    columns: [
      { key: 'name', label: 'Nom', path: 'translations.fr.name', format: 'label' },
      { key: 'proficiency', label: 'Niveau' },
      { key: 'is_visible', label: 'Visible', format: 'boolean' },
      { key: 'position', label: 'Ordre', sortable: true },
    ],
    defaultSort: 'position',
  },
  category('skill-categories', 'Catégories de compétences', 'Catalogue'),

  /* Parcours */
  {
    key: 'experiences',
    label: 'Expériences',
    singular: 'expérience',
    feminine: true,
    icon: Briefcase,
    group: 'Parcours',
    softDeletes: true,
    groups: [
      {
        title: 'Poste',
        fields: [
          { name: 'job_title', label: 'Intitulé du poste', type: 'text', translatable: true, required: true, wide: true },
          { name: 'organization_name', label: 'Organisation', type: 'text', required: true },
          { name: 'organization_url', label: 'Site de l’organisation', type: 'url' },
          { name: 'employment_type', label: 'Type de contrat', type: 'select', options: O.EMPLOYMENT_TYPE, required: true },
          { name: 'is_remote', label: 'À distance', type: 'boolean' },
          { name: 'started_on', label: 'Début', type: 'date', required: true },
          { name: 'is_current', label: 'Poste actuel', type: 'boolean' },
          { name: 'ended_on', label: 'Fin', type: 'date', visibleIf: (values) => !values.is_current },
          { name: 'country_code', label: 'Pays (code ISO : FR, BJ…)', type: 'text' },
          { name: 'location_label', label: 'Lieu', type: 'text', translatable: true },
          { name: 'organization_logo_media_id', label: 'Logo', type: 'media' },
        ],
      },
      {
        title: 'Description',
        fields: [
          { name: 'summary', label: 'Résumé', type: 'textarea', translatable: true, required: true, wide: true },
          { name: 'description', label: 'Missions', type: 'markdown', translatable: true, required: true, wide: true },
          { name: 'achievements', label: 'Réalisations', type: 'markdown', translatable: true, wide: true },
          { name: 'technology_ids', label: 'Technologies', type: 'relations', resource: 'technologies', wide: true },
          ...display(false),
        ],
      },
    ],
    columns: [
      { key: 'job_title', label: 'Poste', path: 'translations.fr.job_title', format: 'label' },
      { key: 'organization_name', label: 'Organisation' },
      { key: 'started_on', label: 'Début', format: 'date', sortable: true },
      { key: 'is_current', label: 'Actuel', format: 'boolean' },
    ],
    defaultSort: '-started_on',
  },
  {
    key: 'educations',
    label: 'Formations',
    singular: 'formation',
    feminine: true,
    icon: GraduationCap,
    group: 'Parcours',
    groups: [
      {
        title: 'Formation',
        fields: [
          { name: 'degree_title', label: 'Diplôme', type: 'text', translatable: true, required: true, wide: true },
          { name: 'field_of_study', label: 'Spécialité', type: 'text', translatable: true },
          { name: 'institution_name', label: 'Établissement', type: 'text', required: true },
          { name: 'institution_url', label: 'Site de l’établissement', type: 'url' },
          { name: 'degree_level', label: 'Niveau', type: 'select', options: O.DEGREE_LEVEL, required: true },
          { name: 'completion_status', label: 'État', type: 'select', options: O.COMPLETION_STATUS, required: true },
          { name: 'started_on', label: 'Début', type: 'date' },
          { name: 'ended_on', label: 'Fin', type: 'date' },
          { name: 'country_code', label: 'Pays (code ISO)', type: 'text' },
          { name: 'location_label', label: 'Lieu', type: 'text', translatable: true },
          { name: 'description', label: 'Description', type: 'markdown', translatable: true, wide: true },
          { name: 'institution_logo_media_id', label: 'Logo', type: 'media' },
          { name: 'proof_media_id', label: 'Justificatif (privé)', type: 'media', mediaKind: 'any', hint: 'Jamais affiché sur le site.' },
          ...display(false),
        ],
      },
    ],
    columns: [
      { key: 'degree_title', label: 'Diplôme', path: 'translations.fr.degree_title', format: 'label' },
      { key: 'institution_name', label: 'Établissement' },
      { key: 'completion_status', label: 'État', format: 'badge', options: O.COMPLETION_STATUS },
    ],
  },
  {
    key: 'certifications',
    label: 'Certifications',
    singular: 'certification',
    feminine: true,
    icon: BadgeCheck,
    group: 'Parcours',
    groups: [
      {
        title: 'Certification',
        fields: [
          { name: 'title', label: 'Intitulé', type: 'text', translatable: true, required: true, wide: true },
          { name: 'issuer_name', label: 'Organisme', type: 'text', required: true },
          { name: 'issuer_url', label: 'Site de l’organisme', type: 'url' },
          { name: 'issued_on', label: 'Obtenue le', type: 'date', required: true },
          { name: 'expires_on', label: 'Expire le', type: 'date' },
          { name: 'credential_id', label: 'Identifiant', type: 'text' },
          { name: 'verification_url', label: 'Lien de vérification', type: 'url' },
          { name: 'description', label: 'Description', type: 'textarea', translatable: true, wide: true },
          { name: 'certificate_media_id', label: 'Certificat', type: 'media', mediaKind: 'any' },
          { name: 'technology_ids', label: 'Technologies', type: 'relations', resource: 'technologies', wide: true },
          ...display(),
        ],
      },
    ],
    columns: [
      { key: 'title', label: 'Intitulé', path: 'translations.fr.title', format: 'label' },
      { key: 'issuer_name', label: 'Organisme' },
      { key: 'issued_on', label: 'Obtenue le', format: 'date' },
    ],
  },
  {
    key: 'achievements',
    label: 'Réalisations',
    singular: 'réalisation',
    feminine: true,
    icon: Trophy,
    group: 'Parcours',
    groups: [
      {
        title: 'Réalisation',
        fields: [
          { name: 'title', label: 'Titre', type: 'text', translatable: true, required: true, wide: true },
          { name: 'type', label: 'Type', type: 'select', options: O.ACHIEVEMENT_TYPE, required: true },
          { name: 'achieved_on', label: 'Date', type: 'date', required: true },
          { name: 'organization_name', label: 'Organisation', type: 'text' },
          { name: 'url', label: 'Lien', type: 'url' },
          { name: 'description', label: 'Description', type: 'textarea', translatable: true, wide: true },
          { name: 'media_id', label: 'Image', type: 'media' },
          ...display(),
        ],
      },
    ],
    columns: [
      { key: 'title', label: 'Titre', path: 'translations.fr.title', format: 'label' },
      { key: 'type', label: 'Type', format: 'badge', options: O.ACHIEVEMENT_TYPE },
      { key: 'achieved_on', label: 'Date', format: 'date' },
    ],
  },
  {
    key: 'timeline-events',
    label: 'Chronologie',
    singular: 'événement',
    icon: Milestone,
    group: 'Parcours',
    groups: [
      {
        title: 'Événement',
        description: 'Lié à une source (expérience, projet…), il en reprend les informations ; le titre sert alors de surcharge.',
        fields: [
          { name: 'type', label: 'Type', type: 'select', options: O.TIMELINE_TYPE, required: true },
          { name: 'event_date', label: 'Date', type: 'date', required: true },
          { name: 'end_date', label: 'Date de fin', type: 'date' },
          { name: 'eventable_type', label: 'Source (même type)', type: 'select', options: O.TIMELINE_SOURCE },
          { name: 'eventable_id', label: 'Identifiant de la source', type: 'number' },
          { name: 'title', label: 'Titre', type: 'text', translatable: true, wide: true },
          { name: 'description', label: 'Description', type: 'textarea', translatable: true, wide: true },
          { name: 'image_media_id', label: 'Image', type: 'media' },
          ...display(false),
        ],
      },
    ],
    columns: [
      { key: 'title', label: 'Titre', path: 'translations.fr.title', format: 'label' },
      { key: 'type', label: 'Type', format: 'badge', options: O.TIMELINE_TYPE },
      { key: 'event_date', label: 'Date', format: 'date', sortable: true },
    ],
  },

  /* Laboratoire */
  {
    key: 'experiments',
    label: 'Expérimentations',
    singular: 'expérimentation',
    feminine: true,
    icon: FlaskConical,
    group: 'Laboratoire',
    contentType: 'experiment',
    softDeletes: true,
    publicPath: publicSlug('lab'),
    groups: [
      {
        title: 'Présentation',
        fields: [
          ...titleSlug(),
          { name: 'summary', label: 'Résumé', type: 'textarea', translatable: true, required: true, wide: true },
          { name: 'description', label: 'Description', type: 'markdown', translatable: true, required: true, wide: true },
          { name: 'objective', label: 'Objectif', type: 'textarea', translatable: true, wide: true },
          { name: 'technical_notes', label: 'Notes techniques', type: 'markdown', translatable: true, wide: true },
        ],
      },
      {
        title: 'Caractéristiques',
        fields: [
          { name: 'experiment_category_id', label: 'Catégorie', type: 'relation', resource: 'experiment-categories', required: true },
          { name: 'kind', label: 'Type', type: 'select', options: O.EXPERIMENT_KIND, required: true },
          { name: 'experimented_on', label: 'Date', type: 'date' },
          { name: 'demo_url', label: 'Démonstration', type: 'url' },
          { name: 'repository_url', label: 'Code source', type: 'url' },
          { name: 'video_url', label: 'Vidéo', type: 'url' },
          {
            name: 'demo_component_key',
            label: 'Composant de démonstration',
            type: 'text',
            hint: 'Clé d’un composant autorisé du site (aucun code n’est exécuté).',
          },
          { name: 'cover_media_id', label: 'Image de couverture', type: 'media' },
          { name: 'technology_ids', label: 'Technologies', type: 'relations', resource: 'technologies', wide: true },
        ],
      },
      editorial(),
    ],
    columns: [
      { key: 'title', label: 'Titre', path: 'translations.fr.title', format: 'label' },
      { key: 'kind', label: 'Type', format: 'badge', options: O.EXPERIMENT_KIND },
      ...statusColumns,
    ],
    filters: [{ name: 'status', label: 'Statut', options: O.PUBLICATION_STATUS }],
  },
  category('experiment-categories', 'Catégories du laboratoire', 'Laboratoire'),

  /* Blog */
  {
    key: 'posts',
    label: 'Articles',
    singular: 'article',
    icon: Newspaper,
    group: 'Blog',
    contentType: 'post',
    softDeletes: true,
    publicPath: publicSlug('blog'),
    groups: [
      {
        title: 'Article',
        fields: [
          ...titleSlug(),
          { name: 'excerpt', label: 'Chapeau', type: 'textarea', translatable: true, required: true, wide: true },
          { name: 'content', label: 'Contenu (Markdown)', type: 'markdown', translatable: true, required: true, wide: true },
        ],
      },
      {
        title: 'Classement',
        fields: [
          { name: 'post_category_id', label: 'Catégorie', type: 'relation', resource: 'post-categories', required: true },
          { name: 'type', label: 'Type', type: 'select', options: O.POST_TYPE, defaultValue: 'article' },
          { name: 'reading_time_minutes', label: 'Temps de lecture (min)', type: 'number', hint: 'Calculé automatiquement si vide.' },
          { name: 'cover_media_id', label: 'Image de couverture', type: 'media' },
          { name: 'tag_ids', label: 'Étiquettes', type: 'relations', resource: 'tags', wide: true },
        ],
      },
      editorial(),
    ],
    columns: [
      { key: 'title', label: 'Titre', path: 'translations.fr.title', format: 'label' },
      { key: 'type', label: 'Type', format: 'badge', options: O.POST_TYPE },
      ...statusColumns,
    ],
    filters: [
      { name: 'status', label: 'Statut', options: O.PUBLICATION_STATUS },
      { name: 'type', label: 'Type', options: O.POST_TYPE },
    ],
  },
  category('post-categories', 'Catégories du blog', 'Blog', [{ name: 'description', label: 'Description', type: 'textarea', translatable: true, wide: true }]),
  {
    key: 'tags',
    label: 'Étiquettes',
    singular: 'étiquette',
    feminine: true,
    icon: Tags,
    group: 'Blog',
    groups: [
      {
        title: 'Étiquette',
        fields: [
          { name: 'name', label: 'Nom', type: 'text', translatable: true, required: true },
          { name: 'slug', label: 'Adresse (slug)', type: 'slug', translatable: true, required: true, slugFrom: 'name' },
        ],
      },
    ],
    columns: [{ key: 'name', label: 'Nom', path: 'translations.fr.name', format: 'label' }],
  },

  /* Communication */
  {
    key: 'testimonials',
    label: 'Témoignages',
    singular: 'témoignage',
    icon: MessageSquareQuote,
    group: 'Communication',
    groups: [
      {
        title: 'Témoignage',
        description: 'Un témoignage n’est publié qu’une fois approuvé, avec le consentement de son auteur.',
        fields: [
          { name: 'quote', label: 'Citation', type: 'textarea', translatable: true, required: true, wide: true },
          { name: 'author_name', label: 'Auteur', type: 'text', required: true },
          { name: 'author_role', label: 'Fonction', type: 'text', translatable: true },
          { name: 'author_company', label: 'Entreprise', type: 'text' },
          { name: 'given_on', label: 'Date', type: 'date' },
          { name: 'project_id', label: 'Projet concerné', type: 'relation', resource: 'projects' },
          { name: 'photo_media_id', label: 'Photo', type: 'media' },
          { name: 'consent_given', label: 'L’auteur a donné son consentement', type: 'boolean' },
          { name: 'status', label: 'Statut', type: 'select', options: O.TESTIMONIAL_STATUS, defaultValue: 'pending' },
          ...display(),
        ],
      },
    ],
    columns: [
      { key: 'author_name', label: 'Auteur', format: 'label' },
      { key: 'author_company', label: 'Entreprise' },
      { key: 'status', label: 'Statut', format: 'status', options: O.TESTIMONIAL_STATUS },
    ],
    filters: [{ name: 'status', label: 'Statut', options: O.TESTIMONIAL_STATUS }],
  },
  {
    key: 'newsletter-campaigns',
    label: 'Campagnes',
    singular: 'campagne',
    feminine: true,
    icon: Megaphone,
    group: 'Communication',
    groups: [
      {
        title: 'Campagne',
        description: 'Rédaction seulement : l’envoi des campagnes est prévu après la V1 (D-19).',
        fields: [
          { name: 'subject', label: 'Objet', type: 'text', required: true, wide: true },
          { name: 'preview_text', label: 'Texte d’aperçu', type: 'text', wide: true },
          { name: 'locale', label: 'Langue', type: 'select', options: O.LOCALE, required: true, defaultValue: 'fr' },
          { name: 'scheduled_at', label: 'Envoi prévu le', type: 'datetime' },
          { name: 'body', label: 'Contenu', type: 'markdown', required: true, wide: true },
        ],
      },
    ],
    columns: [
      { key: 'subject', label: 'Objet', format: 'label' },
      { key: 'locale', label: 'Langue', format: 'badge', options: O.LOCALE },
      { key: 'status', label: 'Statut', format: 'status', options: O.CAMPAIGN_STATUS },
    ],
  },

  /* Site */
  {
    key: 'pages',
    label: 'Pages',
    singular: 'page',
    feminine: true,
    icon: LayoutTemplate,
    group: 'Site',
    contentType: 'page',
    softDeletes: true,
    publicPath: (item) => {
      const key = item.key as string | undefined;
      const slug = (item.translations as Record<string, { slug?: string }> | undefined)?.fr?.slug;

      if (item.kind === 'system' && key) {
        return `${SITE_URL}/fr${key === 'home' ? '' : `/${key}`}`;
      }

      return slug ? `${SITE_URL}/fr/pages/${slug}` : null;
    },
    groups: [
      {
        title: 'Page',
        description: 'Les pages système (accueil, à propos…) ont une adresse fixe ; leurs sections se gèrent dans « Sections de pages ».',
        fields: [
          { name: 'title', label: 'Titre', type: 'text', translatable: true, required: true, wide: true },
          { name: 'slug', label: 'Adresse (slug)', type: 'slug', translatable: true, slugFrom: 'title', hint: 'Pages personnalisées seulement.' },
          { name: 'summary', label: 'Résumé', type: 'textarea', translatable: true, wide: true },
          {
            name: 'key',
            label: 'Clé technique',
            type: 'text',
            required: true,
            only: 'create',
            hint: 'Identifiant unique : lettres minuscules, chiffres, tirets.',
          },
          { name: 'template', label: 'Gabarit', type: 'text' },
          { name: 'status', label: 'Statut', type: 'select', options: O.PUBLICATION_STATUS, defaultValue: 'draft' },
          { name: 'published_at', label: 'Date de publication', type: 'datetime', visibleIf: (values) => values.status === 'scheduled' },
        ],
      },
    ],
    columns: [
      { key: 'title', label: 'Titre', path: 'translations.fr.title', format: 'label' },
      { key: 'key', label: 'Clé', format: 'badge' },
      {
        key: 'kind',
        label: 'Nature',
        format: 'badge',
        options: [
          { value: 'system', label: 'Système' },
          { value: 'custom', label: 'Personnalisée' },
        ],
      },
      { key: 'status', label: 'Statut', format: 'status', options: O.PUBLICATION_STATUS },
    ],
    filters: [{ name: 'status', label: 'Statut', options: O.PUBLICATION_STATUS }],
  },
  {
    key: 'page-sections',
    label: 'Sections de pages',
    singular: 'section',
    feminine: true,
    icon: Blocks,
    group: 'Site',
    groups: [
      {
        title: 'Section',
        fields: [
          { name: 'page_id', label: 'Page', type: 'relation', resource: 'pages', required: true, only: 'create' },
          { name: 'type', label: 'Type', type: 'select', options: O.PAGE_SECTION_TYPE, required: true, defaultValue: 'richtext' },
          { name: 'title', label: 'Titre', type: 'text', translatable: true, wide: true },
          { name: 'subtitle', label: 'Sous-titre', type: 'textarea', translatable: true, wide: true },
          { name: 'body', label: 'Contenu (Markdown)', type: 'markdown', translatable: true, wide: true },
          { name: 'cta_label', label: 'Texte du bouton', type: 'text', translatable: true },
          { name: 'cta_url', label: 'Lien du bouton', type: 'text', translatable: true, placeholder: '/fr/contact ou https://…' },
          { name: 'config', label: 'Configuration (JSON)', type: 'json', wide: true, hint: 'Exemple : {"limit": 6}' },
          { name: 'is_active', label: 'Active', type: 'boolean', defaultValue: true },
          { name: 'position', label: 'Ordre', type: 'number', defaultValue: 0 },
        ],
      },
    ],
    columns: [
      { key: 'title', label: 'Titre', path: 'translations.fr.title', format: 'label' },
      { key: 'type', label: 'Type', format: 'badge', options: O.PAGE_SECTION_TYPE },
      { key: 'page_id', label: 'Page' },
      { key: 'position', label: 'Ordre', sortable: true },
      { key: 'is_active', label: 'Active', format: 'boolean' },
    ],
    defaultSort: 'position',
  },
  {
    key: 'navigation-menus',
    label: 'Menus',
    singular: 'menu',
    icon: Menu,
    group: 'Site',
    groups: [
      {
        title: 'Menu',
        fields: [
          { name: 'name', label: 'Nom', type: 'text', required: true },
          { name: 'slug', label: 'Identifiant', type: 'slug', required: true, slugFrom: 'name' },
          { name: 'location', label: 'Emplacement', type: 'select', options: O.NAVIGATION_LOCATION },
          { name: 'is_active', label: 'Actif', type: 'boolean', defaultValue: true },
        ],
      },
    ],
    columns: [
      { key: 'name', label: 'Nom', format: 'label' },
      { key: 'location', label: 'Emplacement', format: 'badge', options: O.NAVIGATION_LOCATION },
      { key: 'is_active', label: 'Actif', format: 'boolean' },
    ],
  },
  {
    key: 'navigation-items',
    label: 'Éléments de menu',
    singular: 'élément',
    icon: ListTree,
    group: 'Site',
    groups: [
      {
        title: 'Élément de menu',
        fields: [
          { name: 'label', label: 'Libellé', type: 'text', translatable: true, required: true },
          { name: 'navigation_menu_id', label: 'Menu', type: 'relation', resource: 'navigation-menus', required: true },
          { name: 'type', label: 'Destination', type: 'select', options: O.NAVIGATION_ITEM_TYPE, required: true, defaultValue: 'route' },
          { name: 'route_key', label: 'Page fixe', type: 'select', options: O.ROUTE_KEYS, visibleIf: (values) => values.type === 'route' },
          { name: 'page_id', label: 'Page', type: 'relation', resource: 'pages', visibleIf: (values) => values.type === 'page' },
          { name: 'url', label: 'Adresse', type: 'url', visibleIf: (values) => values.type === 'external' },
          { name: 'parent_id', label: 'Élément parent', type: 'relation', resource: 'navigation-items' },
          { name: 'icon', label: 'Icône (clé)', type: 'text' },
          { name: 'opens_in_new_tab', label: 'Ouvrir dans un nouvel onglet', type: 'boolean' },
          ...display(false),
        ],
      },
    ],
    columns: [
      { key: 'label', label: 'Libellé', path: 'translations.fr.label', format: 'label' },
      { key: 'type', label: 'Destination', format: 'badge', options: O.NAVIGATION_ITEM_TYPE },
      { key: 'navigation_menu_id', label: 'Menu' },
      { key: 'position', label: 'Ordre', sortable: true },
    ],
    defaultSort: 'position',
  },
  {
    key: 'social-links',
    label: 'Réseaux sociaux',
    singular: 'lien',
    icon: Link2,
    group: 'Identité',
    groups: [
      {
        title: 'Lien',
        fields: [
          { name: 'platform', label: 'Plateforme', type: 'select', options: O.SOCIAL_PLATFORM, required: true },
          { name: 'url', label: 'Adresse', type: 'text', required: true, placeholder: 'https://… ou mailto:…' },
          ...display(false),
        ],
      },
    ],
    columns: [
      { key: 'platform', label: 'Plateforme', format: 'badge', options: O.SOCIAL_PLATFORM },
      { key: 'url', label: 'Adresse', format: 'label' },
      { key: 'is_visible', label: 'Visible', format: 'boolean' },
      { key: 'position', label: 'Ordre', sortable: true },
    ],
    defaultSort: 'position',
  },
  {
    key: 'redirects',
    label: 'Redirections',
    singular: 'redirection',
    feminine: true,
    icon: Route,
    group: 'Référencement',
    groups: [
      {
        title: 'Redirection',
        description: 'Créées automatiquement quand l’adresse d’un contenu publié change ; ajoutez-en à la main si besoin.',
        fields: [
          { name: 'from_path', label: 'Ancienne adresse', type: 'text', required: true, placeholder: '/fr/ancien-chemin' },
          { name: 'to_path', label: 'Nouvelle adresse', type: 'text', required: true, placeholder: '/fr/projects/nouveau' },
          { name: 'status_code', label: 'Code', type: 'select', options: O.REDIRECT_STATUS, defaultValue: '301' },
          { name: 'is_active', label: 'Active', type: 'boolean', defaultValue: true },
        ],
      },
    ],
    columns: [
      { key: 'from_path', label: 'De', format: 'label' },
      { key: 'to_path', label: 'Vers' },
      { key: 'status_code', label: 'Code', format: 'badge' },
      { key: 'hits', label: 'Visites', sortable: true },
    ],
  },
  {
    key: 'media-collections',
    label: 'Collections',
    singular: 'collection',
    feminine: true,
    icon: Layers,
    group: 'Médias',
    groups: [
      {
        title: 'Collection',
        fields: [
          { name: 'name', label: 'Nom', type: 'text', required: true },
          { name: 'slug', label: 'Identifiant', type: 'slug', required: true, slugFrom: 'name' },
          { name: 'description', label: 'Description', type: 'textarea', wide: true },
          { name: 'position', label: 'Ordre', type: 'number', defaultValue: 0 },
        ],
      },
    ],
    columns: [
      { key: 'name', label: 'Nom', format: 'label' },
      { key: 'position', label: 'Ordre', sortable: true },
    ],
    defaultSort: 'position',
  },
  {
    key: 'resumes',
    label: 'CV',
    singular: 'CV',
    icon: FileText,
    group: 'Identité',
    groups: [
      {
        title: 'CV',
        description: 'Un CV public doit être actif et avoir une version (PDF) courante : ajoutez les versions après la création.',
        fields: [
          { name: 'title', label: 'Titre', type: 'text', required: true, wide: true },
          { name: 'target', label: 'Cible', type: 'select', options: O.RESUME_TARGET, defaultValue: 'general' },
          { name: 'locale', label: 'Langue', type: 'select', options: O.LOCALE, required: true, defaultValue: 'fr' },
          { name: 'status', label: 'Statut', type: 'select', options: O.RESUME_STATUS, defaultValue: 'draft' },
          { name: 'is_public', label: 'Téléchargeable sur le site', type: 'boolean' },
          { name: 'is_default', label: 'CV par défaut de la langue', type: 'boolean' },
        ],
      },
    ],
    columns: [
      { key: 'title', label: 'Titre', format: 'label' },
      { key: 'locale', label: 'Langue', format: 'badge', options: O.LOCALE },
      { key: 'status', label: 'Statut', format: 'status', options: O.RESUME_STATUS },
      { key: 'is_public', label: 'Public', format: 'boolean' },
    ],
  },

  /* Données sensibles (compte admin seulement) */
  {
    key: 'messages',
    label: 'Messages',
    singular: 'message',
    icon: Mail,
    group: 'Communication',
    sensitive: true,
    noCreate: true,
    softDeletes: true,
    searchable: true,
    groups: [
      {
        title: 'Traitement',
        fields: [
          { name: 'status', label: 'Statut', type: 'select', options: O.MESSAGE_STATUS },
          { name: 'internal_note', label: 'Note interne', type: 'textarea', wide: true },
        ],
      },
    ],
    columns: [
      { key: 'name', label: 'De', format: 'label' },
      { key: 'subject', label: 'Objet' },
      { key: 'type', label: 'Type', format: 'badge', options: O.MESSAGE_TYPE },
      { key: 'status', label: 'Statut', format: 'status', options: O.MESSAGE_STATUS },
      { key: 'created_at', label: 'Reçu', format: 'relative', sortable: true },
    ],
    filters: [
      { name: 'status', label: 'Statut', options: O.MESSAGE_STATUS },
      { name: 'type', label: 'Type', options: O.MESSAGE_TYPE },
    ],
  },
  {
    key: 'newsletter-subscribers',
    label: 'Abonnés',
    singular: 'abonné',
    icon: Mail,
    group: 'Communication',
    sensitive: true,
    noCreate: true,
    readOnly: true,
    searchable: true,
    groups: [],
    columns: [
      { key: 'email', label: 'Adresse', format: 'label', sortable: true },
      { key: 'locale', label: 'Langue', format: 'badge', options: O.LOCALE },
      { key: 'status', label: 'Statut', format: 'status', options: O.SUBSCRIBER_STATUS },
      { key: 'created_at', label: 'Inscription', format: 'date', sortable: true },
    ],
    filters: [{ name: 'status', label: 'Statut', options: O.SUBSCRIBER_STATUS }],
  },
  {
    key: 'activity-logs',
    label: 'Journal d’activité',
    singular: 'événement',
    icon: History,
    group: 'Administration',
    sensitive: true,
    noCreate: true,
    readOnly: true,
    groups: [],
    columns: [
      { key: 'event', label: 'Action', format: 'badge', options: O.ACTIVITY_EVENT },
      { key: 'subject_type', label: 'Contenu' },
      { key: 'subject_id', label: 'N°' },
      { key: 'user', label: 'Compte', path: 'user.name' },
      { key: 'created_at', label: 'Date', format: 'datetime', sortable: true },
    ],
    filters: [{ name: 'event', label: 'Action', options: O.ACTIVITY_EVENT }],
  },
  {
    key: 'accounts',
    label: 'Comptes',
    singular: 'compte',
    icon: Award,
    group: 'Administration',
    sensitive: true,
    noCreate: true,
    readOnly: true,
    groups: [],
    columns: [
      { key: 'name', label: 'Nom', format: 'label' },
      { key: 'email', label: 'Adresse' },
      {
        key: 'role',
        label: 'Rôle',
        format: 'badge',
        options: [
          { value: 'admin', label: 'Administrateur' },
          { value: 'viewer', label: 'Lecture seule' },
        ],
      },
      { key: 'last_login_at', label: 'Dernière connexion', format: 'relative' },
    ],
    defaultSort: 'id',
  },
];

export const RESOURCE_MAP = new Map(RESOURCES.map((resource) => [resource.key, resource]));

export function resourceOf(key: string | undefined): ResourceConfig | undefined {
  return key ? RESOURCE_MAP.get(key) : undefined;
}
