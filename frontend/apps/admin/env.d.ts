/// <reference types="vite/client" />

interface ImportMetaEnv {
  /** Origine de l'API Laravel (cookie de session Sanctum). */
  readonly VITE_API_ORIGIN?: string;
  /** Adresse du site public (liens « Voir sur le site »). */
  readonly VITE_SITE_URL?: string;
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
}

declare module '*.module.css' {
  const classes: Readonly<Record<string, string>>;
  export default classes;
}
