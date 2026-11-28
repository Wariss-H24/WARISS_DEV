/**
 * Génère src/schema.d.ts depuis le contrat OpenAPI du backend (D-10).
 * Source : backend/openapi.json, ou la variable d'environnement OPENAPI_SOURCE (fichier ou URL).
 */
import { spawnSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';
import { dirname, resolve } from 'node:path';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const source = process.env.OPENAPI_SOURCE ?? resolve(root, '../../../backend/openapi.json');
const output = resolve(root, 'src/schema.d.ts');

const result = spawnSync('openapi-typescript', [source, '--output', output], {
  cwd: root,
  stdio: 'inherit',
  shell: process.platform === 'win32',
});

process.exit(result.status ?? 1);
