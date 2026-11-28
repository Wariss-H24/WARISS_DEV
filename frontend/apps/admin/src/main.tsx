import '@fontsource-variable/inter';
import '@fontsource-variable/space-grotesk';
import '@fontsource-variable/jetbrains-mono';
import '@wariss/ui/styles.css';
import './styles.css';

import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { TooltipProvider } from '@wariss/ui';
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { RouterProvider } from 'react-router/dom';
import { router } from './app/router';
import { ToastProvider } from './components/Toaster';

// Thème : clair par défaut dans l'administration (design system §3.2), préférence mémorisée.
try {
  const saved = localStorage.getItem('admin-theme');

  if (saved === 'dark' || saved === 'light') {
    document.documentElement.dataset.theme = saved;
  }
} catch {
  // Sans stockage local, le thème clair par défaut s'applique.
}

const queryClient = new QueryClient({
  defaultOptions: {
    queries: { staleTime: 30_000, refetchOnWindowFocus: false, retry: 1 },
  },
});

const root = document.getElementById('root');

if (root) {
  createRoot(root).render(
    <StrictMode>
      <QueryClientProvider client={queryClient}>
        <TooltipProvider delayDuration={300}>
          <ToastProvider>
            <RouterProvider router={router} />
          </ToastProvider>
        </TooltipProvider>
      </QueryClientProvider>
    </StrictMode>,
  );
}
