/**
 * Description déclarative d'un type de contenu de l'administration : une seule description sert
 * la liste, le formulaire, les filtres et les actions. Les règles de validation restent celles de
 * l'API (FormRequest) : les erreurs 422 sont affichées sur les champs correspondants.
 */
import type { LucideIcon } from 'lucide-react';

export interface Option {
  value: string;
  label: string;
}

export type FieldType =
  | 'text'
  | 'textarea'
  | 'markdown'
  | 'number'
  | 'boolean'
  | 'date'
  | 'datetime'
  | 'select'
  | 'relation'
  | 'relations'
  | 'media'
  | 'url'
  | 'color'
  | 'slug'
  | 'json'
  | 'children';

export interface FieldDef {
  /** Nom du champ dans l'API (dans translations.<langue> si `translatable`). */
  name: string;
  label: string;
  type: FieldType;
  /** Champ traduit : onglets FR / EN. */
  translatable?: boolean;
  /** Obligatoire (indication visuelle ; la règle est appliquée par l'API). */
  required?: boolean;
  hint?: string;
  placeholder?: string;
  /** Pleine largeur (par défaut : demi-largeur pour les champs courts). */
  wide?: boolean;
  options?: Option[];
  /** Ressource cible d'une relation (segment d'URL de l'admin : « technologies »). */
  resource?: string;
  /** Champ source d'un slug généré (« title »). */
  slugFrom?: string;
  /** Sous-champs d'une liste d'éléments enfants (fonctionnalités, sections…). */
  fields?: FieldDef[];
  /** Champ affiché seulement à la création, ou seulement à la modification. */
  only?: 'create' | 'update';
  /** Affiché seulement si la condition est vraie (valeurs courantes du formulaire). */
  visibleIf?: (values: Record<string, unknown>) => boolean;
  /** Valeur initiale à la création. */
  defaultValue?: unknown;
  /** Type de média accepté par le sélecteur (image par défaut). */
  mediaKind?: 'image' | 'document' | 'video' | 'icon' | 'any';
}

export interface ColumnDef {
  key: string;
  label: string;
  /** Rendu particulier de la valeur. */
  format?: 'text' | 'label' | 'status' | 'boolean' | 'date' | 'datetime' | 'badge' | 'media' | 'relative';
  /** Chemin de la valeur (« translations.fr.title »), sinon `key`. */
  path?: string;
  sortable?: boolean;
  options?: Option[];
}

export interface FilterDef {
  name: string;
  label: string;
  options: Option[];
}

export interface FieldGroup {
  title: string;
  description?: string;
  fields: FieldDef[];
}

export interface ResourceConfig {
  /** Segment d'URL de l'API et de l'administration : « projects ». */
  key: string;
  label: string;
  singular: string;
  /** Genre grammatical (accords : « Nouvelle catégorie », « Nouveau projet »). */
  feminine?: boolean;
  icon: LucideIcon;
  group: string;
  /** Formulaire organisé en groupes de champs. */
  groups: FieldGroup[];
  columns: ColumnDef[];
  filters?: FilterDef[];
  /** Recherche textuelle disponible (filter[search]). */
  searchable?: boolean;
  defaultSort?: string;
  softDeletes?: boolean;
  /** Lecture seule : ni création ni modification (journal, comptes). */
  readOnly?: boolean;
  /** Création impossible depuis l'administration (messages, abonnés). */
  noCreate?: boolean;
  /** Type de contenu pour l'aperçu signé et la fiche SEO (alias de la morph map). */
  contentType?: 'page' | 'project' | 'case_study' | 'service' | 'experiment' | 'post';
  /** Adresse publique d'un contenu (« Voir sur le site »). */
  publicPath?: (item: Record<string, unknown>) => string | null;
  /** Réservé aux données sensibles (compte admin seulement). */
  sensitive?: boolean;
}
