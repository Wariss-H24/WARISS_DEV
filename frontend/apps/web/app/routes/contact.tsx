import type { Paginated, Service } from '@wariss/api-client';
import { createI18n } from '@wariss/i18n';
import { AuroraBackground, Button, Checkbox, Container, Field, Input, Select, Textarea, Tooltip } from '@wariss/ui';
import { CircleCheck, Clock, Mail, MapPin, Send } from 'lucide-react';
import { useRef, useState, type FormEvent } from 'react';
import { useTranslation } from 'react-i18next';
import { useLoaderData, useRouteLoaderData } from 'react-router';
import type { Route } from './+types/contact';
import { Reveal } from '~/components/Reveal/Reveal';
import { SocialIcon } from '~/components/SocialIcon/SocialIcon';
import { Turnstile } from '~/components/Turnstile/Turnstile';
import { apiGetOr } from '~/lib/api.server';
import { submitPublicForm } from '~/lib/public-form';
import { layoutSite, routeLocale, siteName, siteUrlOf } from '~/lib/route-helpers';
import { buildMeta, staticPaths } from '~/lib/seo';
import { platformLabel, socialLinksOf } from '~/lib/social';
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

/**
 * Page de contact : présentation et coordonnées à gauche, formulaire à droite. Envoi direct du
 * navigateur vers l'API (anti-robot Turnstile, champ piège, 5 envois par minute).
 */
export default function Contact() {
  const { locale, services, initialType, initialService } = useLoaderData<typeof loader>();
  const layout = useRouteLoaderData<typeof layoutLoader>('routes/lang-layout');
  const site = layout?.site ?? null;
  const profile = site?.profile ?? null;
  const email = profile?.public_email ?? (site?.settings.contact?.public_email as string | null | undefined) ?? null;
  const location = profile?.location_label ?? null;
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
    <section className={styles.page} aria-labelledby="contact-title">
      <AuroraBackground grid={false} />
      <Container size="wide" className={styles.layout}>
        <Reveal className={styles.intro}>
          <p className={styles.eyebrow}>{t('contact.eyebrow')}</p>
          <h1 id="contact-title" className={styles.title}>
            {t('contact.heading')}
          </h1>
          <p className={styles.lead}>{t('contact.intro')}</p>

          <ul className={styles.infos}>
            {email ? (
              <li className={styles.info}>
                <span className={styles.infoIcon} aria-hidden="true">
                  <Mail size={18} />
                </span>
                <span>
                  <span className={styles.infoLabel}>{t('contact.emailLabel')}</span>
                  <a href={`mailto:${email}`} className={styles.infoValue}>
                    {email}
                  </a>
                </span>
              </li>
            ) : null}
            {location ? (
              <li className={styles.info}>
                <span className={styles.infoIcon} aria-hidden="true">
                  <MapPin size={18} />
                </span>
                <span>
                  <span className={styles.infoLabel}>{t('contact.location')}</span>
                  <span className={styles.infoValue}>{location}</span>
                </span>
              </li>
            ) : null}
            <li className={styles.info}>
              <span className={styles.infoIcon} aria-hidden="true">
                <Clock size={18} />
              </span>
              <span>
                <span className={styles.infoLabel}>{t('contact.responseTime')}</span>
                <span className={styles.infoValue}>
                  {t('contact.typeContact')} · {t('contact.typeQuote')} · {t('contact.typeCollaboration')}
                </span>
              </span>
            </li>
          </ul>

          <p className={styles.followLabel}>{t('footer.connect')}</p>
          <ul className={styles.socials}>
            {socialLinksOf(site).map((link) => (
              <li key={link.id}>
                <Tooltip content={platformLabel(link.platform)}>
                  <a href={link.url} target="_blank" rel="noopener noreferrer me" className={styles.social} aria-label={platformLabel(link.platform)}>
                    <SocialIcon platform={link.platform} size={18} />
                  </a>
                </Tooltip>
              </li>
            ))}
          </ul>
        </Reveal>

        <Reveal delay={0.08} className={styles.card}>
          {status === 'success' ? (
            <div className={styles.success} role="status">
              <CircleCheck aria-hidden="true" size={44} />
              <p>{message}</p>
              <Button variant="secondary" onClick={() => setStatus('idle')}>
                {t('actions.back')}
              </Button>
            </div>
          ) : (
            <form ref={formRef} onSubmit={submit} noValidate className={styles.form} aria-labelledby="contact-form-title">
              <h2 id="contact-form-title" className={styles.formTitle}>
                {t('contact.formTitle')}
              </h2>

              <fieldset className={styles.types}>
                <legend className="visually-hidden">{t('contact.type')}</legend>
                {(['contact', 'quote', 'collaboration'] as const).map((value) => (
                  <label key={value} className={styles.typeOption} data-checked={type === value || undefined}>
                    <input type="radio" name="type" value={value} checked={type === value} onChange={() => setType(value)} className="visually-hidden" />
                    {t(`contact.type${value.charAt(0).toUpperCase()}${value.slice(1)}` as 'contact.typeContact')}
                  </label>
                ))}
              </fieldset>

              <div className={styles.row}>
                <Field label={t('contact.name')} error={errors.name} required>
                  <Input name="name" autoComplete="name" maxLength={150} placeholder={t('contact.namePlaceholder')} />
                </Field>
                <Field label={t('contact.email')} error={errors.email} required>
                  <Input name="email" type="email" autoComplete="email" inputMode="email" maxLength={255} placeholder={t('contact.emailPlaceholder')} />
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
                <Input name="subject" maxLength={255} placeholder={t('contact.subjectPlaceholder')} />
              </Field>

              <Field label={t('contact.message')} error={errors.body} required>
                <Textarea name="body" rows={6} minLength={10} maxLength={5000} placeholder={t('contact.messagePlaceholder')} />
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
                <p className={styles.formError} role="alert">
                  {message}
                </p>
              ) : null}

              <Button type="submit" size="lg" block loading={status === 'sending'} className={styles.submit}>
                <Send aria-hidden="true" />
                {status === 'sending' ? t('actions.sending') : t('contact.formTitle')}
              </Button>
            </form>
          )}
        </Reveal>
      </Container>
    </section>
  );
}
