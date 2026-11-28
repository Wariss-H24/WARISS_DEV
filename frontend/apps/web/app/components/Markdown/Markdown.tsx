import ReactMarkdown from 'react-markdown';
import rehypeSanitize from 'rehype-sanitize';
import remarkGfm from 'remark-gfm';

/**
 * Rendu d'un contenu Markdown (articles, études de cas, descriptions) : syntaxe GitHub, HTML brut
 * neutralisé par rehype-sanitize (aucun script ni attribut dangereux), liens externes sûrs.
 */
export function Markdown({ children, className = 'prose' }: { children: string | null | undefined; className?: string }) {
  if (!children) {
    return null;
  }

  return (
    <div className={className}>
      <ReactMarkdown
        remarkPlugins={[remarkGfm]}
        rehypePlugins={[rehypeSanitize]}
        components={{
          a: ({ href, children: content, ...props }) => {
            const external = typeof href === 'string' && /^https?:\/\//i.test(href);

            return (
              <a href={href} {...props} {...(external ? { target: '_blank', rel: 'noopener noreferrer' } : {})}>
                {content}
              </a>
            );
          },
          img: ({ src, alt }) => <img src={typeof src === 'string' ? src : undefined} alt={alt ?? ''} loading="lazy" decoding="async" />,
        }}
      >
        {children}
      </ReactMarkdown>
    </div>
  );
}
