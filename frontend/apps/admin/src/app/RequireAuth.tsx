import { Skeleton } from '@wariss/ui';
import { useQueryClient } from '@tanstack/react-query';
import { useEffect, type ReactNode } from 'react';
import { Navigate, useLocation } from 'react-router';
import { ME_KEY, useAbilities, useCurrentUser } from '~/lib/auth';
import { SESSION_EXPIRED_EVENT } from '~/lib/http';

/** Routes réservées aux comptes connectés : redirige vers la connexion, puis revient à la page demandée. */
export function RequireAuth({ children }: { children: ReactNode }) {
  const { data: user, isPending } = useCurrentUser();
  const location = useLocation();
  const client = useQueryClient();

  useEffect(() => {
    const onExpired = () => client.setQueryData(ME_KEY, null);
    window.addEventListener(SESSION_EXPIRED_EVENT, onExpired);

    return () => window.removeEventListener(SESSION_EXPIRED_EVENT, onExpired);
  }, [client]);

  if (isPending) {
    return (
      <div style={{ padding: 'var(--space-8)', display: 'grid', gap: 'var(--space-3)' }} aria-busy="true">
        <Skeleton height="2rem" width="40%" />
        <Skeleton height="12rem" />
      </div>
    );
  }

  if (!user) {
    return <Navigate to="/login" replace state={{ from: location.pathname + location.search }} />;
  }

  return children;
}

/** Données sensibles : page réservée au compte admin. */
export function RequireSensitive({ children }: { children: ReactNode }) {
  const { canViewSensitive } = useAbilities();

  return canViewSensitive ? children : <Navigate to="/" replace />;
}
