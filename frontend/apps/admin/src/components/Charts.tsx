import styles from './Charts.module.css';

interface Point {
  label: string;
  value: number;
}

/**
 * Histogramme léger en SVG (aucune bibliothèque) ; les valeurs sont aussi données dans un tableau
 * masqué pour les lecteurs d'écran.
 */
export function BarChart({ points, label, height = 160 }: { points: Point[]; label: string; height?: number }) {
  const max = Math.max(1, ...points.map((point) => point.value));
  const width = Math.max(points.length * 28, 280);
  const barWidth = (width / Math.max(points.length, 1)) * 0.62;

  return (
    <figure className={styles.chart}>
      <svg viewBox={`0 0 ${width} ${height + 24}`} role="img" aria-label={label} preserveAspectRatio="none" className={styles.svg}>
        {[0.25, 0.5, 0.75, 1].map((ratio) => (
          <line key={ratio} x1="0" x2={width} y1={height - height * ratio} y2={height - height * ratio} className={styles.grid} />
        ))}
        {points.map((point, index) => {
          const barHeight = (point.value / max) * (height - 8);
          const x = (width / points.length) * index + (width / points.length - barWidth) / 2;

          return (
            <g key={point.label}>
              <rect x={x} y={height - barHeight} width={barWidth} height={Math.max(barHeight, point.value ? 2 : 0)} rx="3" className={styles.bar}>
                <title>
                  {point.label} : {point.value}
                </title>
              </rect>
              {points.length <= 14 || index % Math.ceil(points.length / 10) === 0 ? (
                <text x={x + barWidth / 2} y={height + 16} textAnchor="middle" className={styles.axis}>
                  {point.label}
                </text>
              ) : null}
            </g>
          );
        })}
      </svg>
      <table className="visually-hidden">
        <caption>{label}</caption>
        <tbody>
          {points.map((point) => (
            <tr key={point.label}>
              <th scope="row">{point.label}</th>
              <td>{point.value}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </figure>
  );
}

/** Classement horizontal (pages les plus vues, provenances…). */
export function RankList({ items, empty = 'Pas encore de données.' }: { items: Array<{ value: string; count: number }>; empty?: string }) {
  const max = Math.max(1, ...items.map((item) => item.count));

  if (!items.length) {
    return <p className={styles.empty}>{empty}</p>;
  }

  return (
    <ol className={styles.rank}>
      {items.map((item) => (
        <li key={item.value} className={styles.rankItem}>
          <span className={styles.rankBar} style={{ width: `${(item.count / max) * 100}%` }} aria-hidden="true" />
          <span className={styles.rankLabel}>{item.value || '—'}</span>
          <span className={styles.rankCount}>{item.count}</span>
        </li>
      ))}
    </ol>
  );
}
