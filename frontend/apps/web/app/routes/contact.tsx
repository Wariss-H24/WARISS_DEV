import type { Paginated, Service } from '@wariss/api-client';
import { createI18n } from '@wariss/i18n';
import { Badge, Button, Card, Checkbox, Container, Field, Input, Section, Select, Textarea } from '@wariss/ui';
import { CircleCheck, Clock, Mail, Send } from 'lucide-react';
import { useRef, useState, type FormEvent } from 'react';
import { useTranslation } from 'react-i18next';
import { useLoaderData, useRouteLoaderData } from 'react-router';
import type { Route } from './+types/contact';
import { PageHeader } from '~/components/PageHeader/PageHeader';
import { SocialIcon } from '~/components/SocialIcon/SocialIcon';
import { Turnstile } from '~/components/Turnstile/Turnstile';
import { apiGetOr } from '~/lib/api.server';
import { submitPublicForm } from '~/lib/public-form';
import { layoutSite, routeLocale, siteName, siteUrlOf } from '~/lib/route-helpers';
import { buildMeta, staticPaths } from '~/lib/seo';
import type { loader as layoutLoader } from './lang-layout';
import styles from './contact.module.css';

type RequestType = 'contact' | 'quote' | 'collaboration';

export async function loader({ params, request }: Route.LoaderArgs) {
  const locale = routeLocale(params);
  const url = new URL(request.url);
  const type = url.searchParams.get('type');
  const services = await apiGetOr<Paginated<Service>>(
    '/services',
    locale,
    { data: [], meta: { current_page: 1, last_page: 1, per_page: 0, total: 0 } },
    { per_page: 50 },
  );

  return {
    locale,
    services: services.data.map((service) => ({ slug: service.slug, title: service.title })),
    initialType: (['contact', 'quote', 'collaboration'] as const).find((value) => value === type) ?? 'contact',
    initialService: url.searchParams.get('service') ?? '',
  };
}

export function meta({ loaderData, matches }: Route.MetaArgs) {
  const locale = loaderData?.locale ?? 'fr';
  const t = createI18n(locale).t;

  return buildMeta({
    siteUrl: siteUrlOf(matches),
    siteName: siteName(layoutSite(matches)),
    locale,
    title: t('contact.title'),
    description: t('contact.description'),
    paths: staticPaths('contact'),
  });
}

export default function Contact() {
  const { locale, services, initialType, initialService } = useLoaderData<typeof loader>();
  const layout = useRouteLoaderData<typeof layoutLoader>('routes/lang-layout');
  const profile = layout?.site?.profile ?? null;
  const { t } = useTranslation();

  const [type, setType] = useState<RequestType>(initialType);
  const [consent, setConsent] = useState(false);
  const [token, setToken] = useState<string | null>(null);
  const [resetKey, setResetKey] = useState(0);
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [status, setStatus] = useState<'idle' | 'sending' | 'success' | 'error'>('idle');
  const [message, setMessage] = useState<string | null>(null);
  const formRef = useRef<HTMLFormElement>(null);

  const submit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    setStatus('sending');
    setErrors({});

    const result = await submitPublicForm('contact', locale, {
      type,
      name: form.get('name'),
      email: form.get('email'),
      subject: form.get('subject') || null,
      body: form.get('body'),
      service: form.get('service') || null,
      details: type === 'quote' ? { budget: form.get('budget') || null, deadline: form.get('deadline') || null } : undefined,
      consent,
      website: form.get('website'),
      turnstile_token: token,
    });

    if (result.status === 'success') {
      setStatus('success');
      setMessage(result.message || t('contact.success'));
      formRef.current?.reset();
      setConsent(false);
    } else if (result.status === 'invalid') {
      setStatus('error');
      setErrors(result.errors);
      setMessage(t('contact.error'));
      const firstField = Object.keys(result.errors)[0];
      formRef.current?.querySelector<HTMLElement>(`[name="${firstField}"]`)?.focus();
    } else {
      setStatus('error');
      setMessage(result.message ?? t('contact.networkError'));
    }

    setResetKey((value) => value + 1);
    setToken(null);
  };

  return (
    <>
      <PageHeader eyebrow={t('contact.eyebrow')} title={t('contact.title')} description={t('contact.description')}>
        <Badge tone="success" dot="pulse">
          <Clock aria-hidden="true" />
          {t('contact.responseTime')}
        </Badge>
      </PageHeader>

      <Section>
        <Container size="wide" className={styles.layout}>
          <Card padded className={styles.formCard}>
            {status === 'success' ? (
              <div className={styles.success} role="status">
                <CircleCheck aria-hidden="true" size={40} />
                <p>{message}</p>
                <Button variant="secondary" onClick={() => setStatus('idle')}>
                  {t('actions.back')}
                </Button>
              </div>
            ) : (
              <form ref={formRef} onSubmit={submit} noValidate className={styles.form} aria-describedby={message ? 'contact-status' : undefined}>
                <fieldset className={styles.types}>
                  <legend className={styles.legend}>{t('contact.type')}</legend>
                  {(['contact', 'quote', 'collaboration'] as const).map((value) => (
                    <label key={value} className={styles.typeOption} data-checked={type === value || undefined}>
                      <input type="radio" name="type" value={value} checked={type === value} onChange={() => setType(value)} className="visually-hidden" />
                      {t(`contact.type${value.charAt(0).toUpperCase()}${value.slice(1)}` as 'contact.typeContact')}
                    </label>
                  ))}
                </fieldset>

                <div className={styles.row}>
                  <Field label={t('contact.name')} error={errors.name} required>
                    <Input name="name" autoComplete="name" maxLength={150} />
                  </Field>
                  <Field label={t('contact.email')} error={errors.email} required>
                    <Input name="email" type="email" autoComplete="email" inputMode="email" maxLength={255} />
                  </Field>
                </div>

                {type !== 'contact' && services.length ? (
                  <Field label={t('contact.service')} error={errors.service}>
                    <Select name="service" defaultValue={initialService}>
                      <option value="">{t('contact.noService')}</option>
                      {services.map((service) => (
                        <option key={service.slug} value={service.slug}>
                          {service.title}
                        </option>
                      ))}
                    </Select>
                  </Field>
                ) : null}

                {type === 'quote' ? (
                  <div className={styles.row}>
                    <Field label={t('contact.budget')} error={errors.details}>
                      <Input name="budget" maxLength={255} />
                    </Field>
                    <Field label={t('contact.deadline')}>
                      <Input name="deadline" maxLength={255} />
                    </Field>
                  </div>
                ) : null}

                <Field label={t('contact.subject')} error={errors.subject}>
                  <Input name="subject" maxLength={255} />
                </Field>

                <Field label={t('contact.message')} hint={t('contact.messageHint')} error={errors.body} required>
                  <Textarea name="body" rows={7} minLength={10} maxLength={5000} />
                </Field>

                {/* Champ piège : invisible pour un humain, rempli par les robots. */}
                <div className={styles.trap} aria-hidden="true">
                  <label>
                    Website
                    <input type="text" name="website" tabIndex={-1} autoComplete="off" />
                  </label>
                </div>

                <Checkbox label={t('contact.consent')} checked={consent} onCheckedChange={setConsent} invalid={Boolean(errors.consent)} required />
                {errors.consent ? (
                  <p className={styles.fieldError} role="alert">
                    {errors.consent}
                  </p>
                ) : null}

                <Turnstile onToken={setToken} locale={locale} resetKey={resetKey} />
                {errors.turnstile_token ? (
                  <p className={styles.fieldError} role="alert">
                    {errors.turnstile_token}
                  </p>
                ) : null}

                {status === 'error' && message ? (
                  <p id="contact-status" className={styles.formError} role="alert">
                    {message}
                  </p>
                ) : null}

                <Button type="submit" variant="gradient" size="lg" loading={status === 'sending'}>
                  <Send aria-hidden="true" />
                  {status === 'sending' ? t('actions.sending') : t('actions.send')}
                </Button>
              </form>
            )}
          </Card>

          <aside className={styles.aside}>
            {profile?.public_email ? (
              <Card padded>
                <p className="mono-label">{t('contact.directEmail')}</p>
                <a href={`mailto:${profile.public_email}`} className={styles.email}>
                  <Mail aria-hidden="true" size={18} />
                  {profile.public_email}
                </a>
              </Card>
            ) : null}
            {profile?.social_links.length ? (
              <Card padded>
                <p className="mono-label">{t('footer.connect')}</p>
                <ul className={styles.socials}>
                  {profile.social_links.map((link) => (
                    <li key={link.id}>
                      <a href={link.url} target="_blank" rel="noopener noreferrer me" className={styles.social}>
                        <SocialIcon platform={link.platform} size={16} />
                        <span>{link.platform}</span>
                      </a>
                    </li>
                  ))}
                </ul>
              </Card>
            ) : null}
          </aside>
        </Container>
      </Section>
    </>
  );
}
