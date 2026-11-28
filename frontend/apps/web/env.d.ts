/// <reference types="vite/client" />

interface ImportMetaEnv {
  /** URL de l'API vue depuis le navigateur (mesure d'audience, recherche, formulaires). */
  readonly VITE_API_URL?: string;
  /** Clé publique Cloudflare Turnstile (vide en local). */
  readonly VITE_TURNSTILE_SITE_KEY?: string;
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
}

declare module '*.module.css' {
  const classes: Readonly<Record<string, string>>;
  export default classes;
}
