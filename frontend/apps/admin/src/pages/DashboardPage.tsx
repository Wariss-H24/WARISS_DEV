import { useQuery } from '@tanstack/react-query';
import { Badge, Button, Card, Skeleton } from '@wariss/ui';
import { ArrowRight, Eye, Inbox, MessageSquareQuote, Share2, Users } from 'lucide-react';
import { Link } from 'react-router';
import { BarChart, RankList } from '~/components/Charts';
import { PageTitle } from '~/components/PageTitle';
import { ACTIVITY_EVENT, optionLabel } from '~/crud/options';
import { useCurrentUser } from '~/lib/auth';
import { formatNumber, formatRelative } from '~/lib/format';
import { errorMessage, http } from '~/lib/http';
import styles from './DashboardPage.module.css';

interface Dashboard {
  contents: Record<string, { draft: number; scheduled: number; published: number; archived: number }>;
  testimonials_pending: number;
  audience: {
    totals: { page_views: number; visitors: number; shares: number; events: Record<string, number> };
    daily: Array<{ date: string; page_views: number; visitors: number; shares: number }>;
    top_paths: Array<{ value: string; count: number }>;
  };
  inbox?: { messages_new: number; messages_in_progress: number; subscribers: number; subscribers_pending: number };
  recent_activity?: Array<{
    id: number;
    event: string;
    subject_type: string | null;
    subject_id: number | null;
    description: string | null;
    user: string | null;
    created_at: string;
  }>;
}

const CONTENT_LABELS: Record<string, [string, string]> = {
  pages: ['Pages', 'pages'],
  projects: ['Projets', 'projects'],
  case_studies: ['Études de cas', 'case-studies'],
  services: ['Services', 'services'],
  experiments: ['Laboratoire', 'experiments'],
  posts: ['Articles', 'posts'],
};

function Stat({ icon: Icon, label, value, to }: { icon: typeof Eye; label: string; value: number; to?: string }) {
  const body = (
    <>
      <span className={styles.statIcon} aria-hidden="true">
        <Icon size={18} />
      </span>
      <span className={styles.statValue}>{formatNumber(value)}</span>
      <span className={styles.statLabel}>{label}</span>
    </>
  );

  return (
    <Card className={styles.stat}>
      {to ? (
        <Link to={to} className={styles.statLink}>
          {body}
        </Link>
      ) : (
        <div className={styles.statLink}>{body}</div>
      )}
    </Card>
  );
}

export function DashboardPage() {
  const { data: user } = useCurrentUser();
  const dashboard = useQuery({ queryKey: ['admin', 'dashboard'], queryFn: async () => (await http.get<{ data: Dashboard }>('/admin/dashboard')).data.data });
  const hour = new Date().getHours();
  const greeting = hour < 12 ? 'Bonjour' : hour < 18 ? 'Bon après-midi' : 'Bonsoir';

  return (
    <>
      <PageTitle
        title={`${greeting}, ${user?.name.split(' ')[0] ?? ''}`}
        description="Vue d’ensemble du site : contenus, audience des 7 derniers jours et activité."
      />

      {dashboard.isPending ? (
        <div className={styles.stats}>
          {Array.from({ length: 4 }, (_, index) => (
            <Skeleton key={index} height="7rem" />
          ))}
        </div>
      ) : dashboard.isError ? (
        <Card padded>
          <p role="alert">{errorMessage(dashboard.error)}</p>
        </Card>
      ) : (
        <>
          <div className={styles.stats}>
            <Stat icon={Eye} label="pages vues (7 j)" value={dashboard.data.audience.totals.page_views} to="/analytics" />
            <Stat icon={Users} label="visiteurs du jour cumulés" value={dashboard.data.audience.totals.visitors} to="/analytics" />
            <Stat icon={Share2} label="partages (7 j)" value={dashboard.data.audience.totals.shares} to="/analytics" />
            {dashboard.data.inbox ? (
              <Stat icon={Inbox} label="nouveaux messages" value={dashboard.data.inbox.messages_new} to="/r/messages?status=new" />
            ) : (
              <Stat icon={MessageSquareQuote} label="témoignages à modérer" value={dashboard.data.testimonials_pending} to="/r/testimonials?status=pending" />
            )}
          </div>

          <div className={styles.columns}>
            <Card padded className={styles.panel}>
              <div className={styles.panelHead}>
                <h2>Audience</h2>
                <Button asChild variant="ghost" size="sm">
                  <Link to="/analytics">
                    Détails
                    <ArrowRight aria-hidden="true" />
                  </Link>
                </Button>
              </div>
              <BarChart
                label="Pages vues par jour, 7 derniers jours"
                points={dashboard.data.audience.daily.map((day) => ({
                  label: new Intl.DateTimeFormat('fr-FR', { weekday: 'short', day: 'numeric' }).format(new Date(`${day.date}T12:00:00Z`)),
                  value: day.page_views,
                }))}
              />
              <h3 className={styles.subTitle}>Pages les plus vues</h3>
              <RankList items={dashboard.data.audience.top_paths} />
            </Card>

            <Card padded className={styles.panel}>
              <div className={styles.panelHead}>
                <h2>Contenus</h2>
              </div>
              <ul className={styles.contents}>
                {Object.entries(dashboard.data.contents).map(([key, counts]) => {
                  const [label, resource] = CONTENT_LABELS[key] ?? [key, key];

                  return (
                    <li key={key}>
                      <Link to={`/r/${resource}`} className={styles.contentRow}>
                        <span className={styles.contentLabel}>{label}</span>
                        <span className={styles.contentCounts}>
                          <Badge tone="success">{counts.published} publiés</Badge>
                          {counts.draft ? <Badge>{counts.draft} brouillons</Badge> : null}
                          {counts.scheduled ? <Badge tone="accent">{counts.scheduled} programmés</Badge> : null}
                        </span>
                      </Link>
                    </li>
                  );
                })}
              </ul>
              {dashboard.data.testimonials_pending ? (
                <Link to="/r/testimonials?status=pending" className={styles.notice}>
                  <MessageSquareQuote aria-hidden="true" size={16} />
                  {dashboard.data.testimonials_pending} témoignage(s) à modérer
                </Link>
              ) : null}
              {dashboard.data.inbox ? (
                <p className={styles.inbox}>
                  {dashboard.data.inbox.subscribers} abonné(s) à la newsletter · {dashboard.data.inbox.subscribers_pending} en attente de confirmation
                </p>
              ) : null}
            </Card>
          </div>

          {dashboard.data.recent_activity?.length ? (
            <Card padded className={styles.panel}>
              <div className={styles.panelHead}>
                <h2>Dernières actions</h2>
                <Button asChild variant="ghost" size="sm">
                  <Link to="/r/activity-logs">
                    Journal complet
                    <ArrowRight aria-hidden="true" />
                  </Link>
                </Button>
              </div>
              <ol className={styles.activity}>
                {dashboard.data.recent_activity.map((entry) => (
                  <li key={entry.id}>
                    <Badge mono>{optionLabel(ACTIVITY_EVENT, entry.event)}</Badge>
                    <span className={styles.activityText}>
                      {entry.subject_type ? `${entry.subject_type} #${entry.subject_id}` : (entry.description ?? '')}
                      {entry.user ? ` · ${entry.user}` : ''}
                    </span>
                    <time dateTime={entry.created_at} className={styles.activityTime}>
                      {formatRelative(entry.created_at)}
                    </time>
                  </li>
                ))}
              </ol>
            </Card>
          ) : null}
        </>
      )}
    </>
  );
}
