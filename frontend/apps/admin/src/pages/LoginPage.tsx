import { Button, Card, Checkbox, Field, Input } from '@wariss/ui';
import { LockKeyhole, LogIn } from 'lucide-react';
import { useEffect, useState, type FormEvent } from 'react';
import { Navigate, useLocation, useNavigate } from 'react-router';
import { useCurrentUser, useLogin } from '~/lib/auth';
import { errorMessage, validationErrors } from '~/lib/http';
import styles from './LoginPage.module.css';

/**
 * Connexion à l'administration (Sanctum, cookie de session). Après 5 échecs, l'API bloque la
 * tentative : le message du serveur est affiché tel quel.
 */
export function LoginPage() {
  const { data: user } = useCurrentUser();
  const login = useLogin();
  const navigate = useNavigate();
  const location = useLocation();
  const [remember, setRemember] = useState(true);
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [message, setMessage] = useState<string | null>(null);
  const from = (location.state as { from?: string } | null)?.from ?? '/';

  useEffect(() => {
    document.title = 'Connexion · Administration WARISS.DEV';
  }, []);

  if (user) {
    return <Navigate to={from} replace />;
  }

  const submit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    setErrors({});
    setMessage(null);

    login.mutate(
      { email: String(form.get('email') ?? ''), password: String(form.get('password') ?? ''), remember },
      {
        onSuccess: () => navigate(from, { replace: true }),
        onError: (error) => {
          const fields = validationErrors(error);

          if (fields) {
            setErrors(fields);
          } else {
            setMessage(errorMessage(error));
          }
        },
      },
    );
  };

  return (
    <main className={styles.page}>
      <div className={styles.glow} aria-hidden="true" />
      <Card padded className={styles.card}>
        <div className={styles.head}>
          <span className={styles.icon} aria-hidden="true">
            <LockKeyhole size={22} />
          </span>
          <h1 className={styles.title}>Administration</h1>
          <p className={styles.subtitle}>wariss.dev · accès réservé</p>
        </div>
        <form onSubmit={submit} noValidate className={styles.form}>
          <Field label="Adresse e-mail" error={errors.email} required>
            <Input name="email" type="email" autoComplete="username" inputMode="email" autoFocus />
          </Field>
          <Field label="Mot de passe" error={errors.password} required>
            <Input name="password" type="password" autoComplete="current-password" />
          </Field>
          <Checkbox label="Rester connecté" checked={remember} onCheckedChange={setRemember} />
          {message ? (
            <p className={styles.error} role="alert">
              {message}
            </p>
          ) : null}
          <Button type="submit" block size="lg" loading={login.isPending}>
            <LogIn aria-hidden="true" />
            Se connecter
          </Button>
        </form>
      </Card>
    </main>
  );
}
