import { CircleCheck, CircleX, Info, X } from 'lucide-react';
import { createContext, useCallback, useContext, useMemo, useState, type ReactNode } from 'react';
import styles from './Toaster.module.css';

type Tone = 'success' | 'error' | 'info';

interface Toast {
  id: number;
  tone: Tone;
  message: string;
}

interface ToastApi {
  success: (message: string) => void;
  error: (message: string) => void;
  info: (message: string) => void;
}

const ToastContext = createContext<ToastApi | null>(null);
let nextId = 1;

/**
 * Notifications de l'administration : annoncées aux lecteurs d'écran (role="status" / "alert"),
 * fermées automatiquement après 5 secondes (8 pour une erreur).
 */
export function ToastProvider({ children }: { children: ReactNode }) {
  const [toasts, setToasts] = useState<Toast[]>([]);

  const dismiss = useCallback((id: number) => setToasts((list) => list.filter((toast) => toast.id !== id)), []);

  const push = useCallback(
    (tone: Tone, message: string) => {
      const id = nextId++;
      setToasts((list) => [...list.slice(-3), { id, tone, message }]);
      window.setTimeout(() => dismiss(id), tone === 'error' ? 8000 : 5000);
    },
    [dismiss],
  );

  const api = useMemo<ToastApi>(
    () => ({ success: (message) => push('success', message), error: (message) => push('error', message), info: (message) => push('info', message) }),
    [push],
  );

  return (
    <ToastContext.Provider value={api}>
      {children}
      <div className={styles.region} aria-live="polite">
        {toasts.map((toast) => {
          const Icon = toast.tone === 'success' ? CircleCheck : toast.tone === 'error' ? CircleX : Info;

          return (
            <div key={toast.id} className={styles.toast} data-tone={toast.tone} role={toast.tone === 'error' ? 'alert' : 'status'}>
              <Icon aria-hidden="true" size={18} className={styles.icon} />
              <p>{toast.message}</p>
              <button type="button" className={styles.close} onClick={() => dismiss(toast.id)} aria-label="Fermer la notification">
                <X aria-hidden="true" size={14} />
              </button>
            </div>
          );
        })}
      </div>
    </ToastContext.Provider>
  );
}

export function useToast(): ToastApi {
  const context = useContext(ToastContext);

  if (!context) {
    throw new Error('useToast doit être utilisé dans <ToastProvider>.');
  }

  return context;
}
