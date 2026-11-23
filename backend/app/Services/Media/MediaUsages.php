<?php

namespace App\Services\Media;

use App\Models\Media\Media;
use Illuminate\Support\Facades\DB;

/**
 * Recense les contenus qui utilisent un média, à partir des clés étrangères de la base
 * vers la table media (couvertures, logos, icônes, pièces jointes, versions de CV, galeries…).
 *
 * La liste est lue dans le catalogue PostgreSQL : une nouvelle colonne media_id est prise
 * en compte sans modifier ce code.
 */
class MediaUsages
{
    /**
     * Tables techniques qui ne sont pas des usages (traductions du média lui-même).
     */
    private const IGNORED_TABLES = ['media_translations'];

    /**
     * @var list<array{table: string, column: string, blocking: bool}>|null
     */
    private ?array $references = null;

    /**
     * @return list<array{table: string, column: string, ids: list<int>, count: int, blocking: bool}>
     */
    public function for(Media $media): array
    {
        $usages = [];

        foreach ($this->references() as $reference) {
            $query = DB::table($reference['table'])->where($reference['column'], $media->getKey());
            $count = $query->count();

            if ($count === 0) {
                continue;
            }

            $idColumn = $reference['table'] === 'mediables' ? 'mediable_id' : 'id';

            $usages[] = [
                ...$reference,
                'ids' => $query->limit(20)->pluck($idColumn)->map(fn (mixed $id): int => (int) $id)->all(),
                'count' => $count,
            ];
        }

        return $usages;
    }

    /**
     * Clés étrangères vers media. « blocking » : la base refuse la suppression (RESTRICT / NO ACTION) ;
     * sinon la référence est vidée (SET NULL) ou supprimée (CASCADE).
     *
     * @return list<array{table: string, column: string, blocking: bool}>
     */
    private function references(): array
    {
        return $this->references ??= collect(DB::select(<<<'SQL'
            SELECT c.conrelid::regclass::text AS table_name, a.attname AS column_name, c.confdeltype AS delete_rule
            FROM pg_constraint c
            JOIN pg_attribute a ON a.attrelid = c.conrelid AND a.attnum = c.conkey[1]
            WHERE c.contype = 'f' AND c.confrelid = 'media'::regclass AND array_length(c.conkey, 1) = 1
            ORDER BY 1, 2
        SQL))
            ->reject(fn (object $row): bool => in_array($row->table_name, self::IGNORED_TABLES, true))
            ->map(fn (object $row): array => [
                'table' => (string) $row->table_name,
                'column' => (string) $row->column_name,
                'blocking' => in_array($row->delete_rule, ['r', 'a'], true),
            ])
            ->values()
            ->all();
    }
}
