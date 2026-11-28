import { useQuery } from '@tanstack/react-query';
import { Card, Select, Skeleton } from '@wariss/ui';
import { useSearchParams } from 'react-router';
import { BarChart, RankList } from '~/components/Charts';
import { PageTitle } from '~/components/PageTitle';
import { formatNumber } from '~/lib/format';
import { errorMessage, http } from '~/lib/http';
import styles from './DashboardPage.module.css';

interface Report {
  period: { from: string; to: string };
  totals: { page_views: number; visitors: number; shares: number; events: Record<string, number> };
  daily: Array<{ date: string; page_views: number; visitors: number; shares: number }>;
  top: Record<string, Array<{ value: string; count: number }>>;
}

const PERIODS = [
  { days: 7, label: '7 derniers jours' },
  { days: 30, label: '30 derniers jours' },
  { days: 90, label: '90 derniers jours' },
  { days: 365, label: '12 derniers mois' },
];

const BREAKDOWNS: Array<[string, string]> = [
  ['paths', 'Pages les plus vues'],
  ['contents', 'Contenus les plus vus'],
  ['referrer_types', 'Provenance'],
  ['referrer_hosts', 'Sites référents'],
  ['devices', 'Appareils'],
  ['countries', 'Pays'],
  ['locales', 'Langues'],
  ['utm_sources', 'Sources de campagne'],
  ['share_networks', 'Partages par réseau'],
  ['shared_contents', 'Contenus les plus partagés'],
];

const EVENT_LABELS: Record<string, string> = {
  cv_download: 'Téléchargements du CV',
  contact_submit: 'Messages envoyés',
  newsletter_subscribe: 'Inscriptions newsletter',
  cta_click: 'Clics sur un appel à l’action',
  demo_open: 'Démonstrations ouvertes',
  outbound_click: 'Liens sortants',
  share: 'Partages',
};

function isoDay(offset: number): string {
  const date = new Date();
  date.setUTCDate(date.getUTCDate() - offset);

  return date.toISOString().slice(0, 10);
}

/** Statistiques d'audience sans cookie (D17) : agrégats quotidiens + journée en cours. */
export function AnalyticsPage() {
  const [params, setParams] = useSearchParams();
  const days = Number(params.get('days') ?? '30') || 30;
  const from = isoDay(days - 1);
  const to = isoDay(0);
  const report = useQuery({
    queryKey: ['admin', 'analytics', from, to],
    queryFn: async () => (await http.get<{ data: Report }>(`/admin/analytics?from=${from}&to=${to}&limit=10`)).data.data,
  });

  return (
    <>
      <PageTitle
        title="Statistiques"
        description="Mesure d’audience sans cookie ni donnée personnelle : les visiteurs sont comptés une fois par jour de visite."
        breadcrumbs={[{ label: 'Tableau de bord', to: '/' }, { label: 'Statistiques' }]}
        actions={
          <Select value={String(days)} onChange={(event) => setParams({ days: event.target.value })} aria-label="Période">
            {PERIODS.map((period) => (
              <option key={period.days} value={period.days}>
                {period.label}
              </option>
            ))}
          </Select>
        }
      />

      {report.isPending ? (
        <Skeleton height="20rem" />
      ) : report.isError ? (
        <Card padded>
          <p role="alert">{errorMessage(report.error)}</p>
        </Card>
      ) : (
        <>
          <div className={styles.stats}>
            {(
              [
                ['Pages vues', report.data.totals.page_views],
                ['Visiteurs (par jour)', report.data.totals.visitors],
                ['Partages', report.data.totals.shares],
                ['Téléchargements du CV', report.data.totals.events.cv_download ?? 0],
              ] as const
            ).map(([label, value]) => (
              <Card key={label} padded>
                <p className={styles.statValue}>{formatNumber(value)}</p>
                <p className={styles.statLabel}>{label}</p>
              </Card>
            ))}
          </div>

          <Card padded className={styles.panel} style={{ marginBottom: 'var(--space-6)' }}>
            <h2 className={styles.subTitle}>Pages vues par jour</h2>
            <BarChart
              label={`Pages vues par jour du ${from} au ${to}`}
              points={report.data.daily.map((day) => ({
                label: new Intl.DateTimeFormat('fr-FR', days > 31 ? { month: 'short', day: 'numeric' } : { day: 'numeric', month: 'numeric' }).format(
                  new Date(`${day.date}T12:00:00Z`),
                ),
                value: day.page_views,
              }))}
            />
          </Card>

          <Card padded className={styles.panel} style={{ marginBottom: 'var(--space-6)' }}>
            <h2 className={styles.subTitle}>Événements</h2>
            <RankList
              items={Object.entries(report.data.totals.events)
                .map(([value, count]) => ({ value: EVENT_LABELS[value] ?? value, count }))
                .sort((a, b) => b.count - a.count)}
              empty="Aucun événement sur la période."
            />
          </Card>

          <div className={styles.columns} style={{ gridTemplateColumns: 'repeat(auto-fit, minmax(20rem, 1fr))' }}>
            {BREAKDOWNS.map(([key, label]) => (
              <Card key={key} padded className={styles.panel}>
                <h2 className={styles.subTitle}>{label}</h2>
                <RankList items={report.data.top[key] ?? []} />
              </Card>
            ))}
          </div>
        </>
      )}
    </>
  );
}
