/**
 * Navigation de l'administration (cahier des charges §23).
 */
import { BarChart3, Image, LayoutDashboard, Settings, UserRound, type LucideIcon } from 'lucide-react';
import { RESOURCE_MAP } from '~/crud/resources';

export interface NavEntry {
  label: string;
  to: string;
  icon: LucideIcon;
  sensitive?: boolean;
}

export interface NavGroup {
  title: string;
  entries: NavEntry[];
}

function resource(key: string, label?: string): NavEntry {
  const config = RESOURCE_MAP.get(key);

  if (!config) {
    throw new Error(`Ressource inconnue : ${key}`);
  }

  return { label: label ?? config.label, to: `/r/${key}`, icon: config.icon, sensitive: config.sensitive };
}

export const NAVIGATION: NavGroup[] = [
  {
    title: '',
    entries: [
      { label: 'Tableau de bord', to: '/', icon: LayoutDashboard },
      { label: 'Statistiques', to: '/analytics', icon: BarChart3 },
    ],
  },
  {
    title: 'Identité',
    entries: [{ label: 'Profil', to: '/profile', icon: UserRound }, resource('social-links'), resource('resumes')],
  },
  {
    title: 'Contenus',
    entries: [resource('projects'), resource('case-studies'), resource('services'), resource('experiments'), resource('posts')],
  },
  {
    title: 'Parcours',
    entries: [resource('experiences'), resource('educations'), resource('certifications'), resource('achievements'), resource('timeline-events')],
  },
  {
    title: 'Catalogue',
    entries: [resource('technologies'), resource('skills')],
  },
  {
    title: 'Classement',
    entries: [
      resource('project-categories', 'Catégories · projets'),
      resource('service-categories', 'Catégories · services'),
      resource('experiment-categories', 'Catégories · laboratoire'),
      resource('post-categories', 'Catégories · blog'),
      resource('technology-categories', 'Catégories · technologies'),
      resource('skill-categories', 'Catégories · compétences'),
      resource('tags'),
    ],
  },
  {
    title: 'Communication',
    entries: [resource('messages'), resource('testimonials'), resource('newsletter-subscribers'), resource('newsletter-campaigns')],
  },
  {
    title: 'Site',
    entries: [
      { label: 'Réglages', to: '/settings', icon: Settings },
      resource('pages'),
      resource('page-sections'),
      resource('navigation-menus'),
      resource('navigation-items'),
      resource('redirects'),
    ],
  },
  {
    title: 'Médias',
    entries: [{ label: 'Bibliothèque', to: '/media', icon: Image }, resource('media-collections')],
  },
  {
    title: 'Administration',
    entries: [resource('activity-logs'), resource('accounts')],
  },
];
