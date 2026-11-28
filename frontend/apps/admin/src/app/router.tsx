import { createBrowserRouter, Navigate } from 'react-router';
import { AdminLayout } from '~/layouts/AdminLayout';
import { LoginPage } from '~/pages/LoginPage';
import { RequireAuth } from './RequireAuth';

/**
 * Routes de l'administration. « /r/:ressource » sert les 30 types de contenus décrits dans
 * crud/resources.ts (liste, création, modification). Chaque page est chargée à la demande.
 */
export const router = createBrowserRouter([
  { path: '/login', element: <LoginPage /> },
  {
    path: '/',
    element: (
      <RequireAuth>
        <AdminLayout />
      </RequireAuth>
    ),
    children: [
      { index: true, lazy: async () => ({ Component: (await import('~/pages/DashboardPage')).DashboardPage }) },
      { path: 'analytics', lazy: async () => ({ Component: (await import('~/pages/AnalyticsPage')).AnalyticsPage }) },
      { path: 'media', lazy: async () => ({ Component: (await import('~/pages/MediaLibraryPage')).MediaLibraryPage }) },
      { path: 'profile', lazy: async () => ({ Component: (await import('~/pages/ProfilePage')).ProfilePage }) },
      { path: 'settings', lazy: async () => ({ Component: (await import('~/pages/SettingsPage')).SettingsPage }) },
      { path: 'r/:resource', lazy: async () => ({ Component: (await import('~/crud/ResourceListPage')).ResourceListPage }) },
      { path: 'r/:resource/:id', lazy: async () => ({ Component: (await import('~/crud/ResourceFormPage')).ResourceFormPage }) },
      { path: '*', element: <Navigate to="/" replace /> },
    ],
  },
]);
