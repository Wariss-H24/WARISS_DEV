<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\SaveModel;
use App\Enums\ActivityEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminFormRequest;
use App\Http\Resources\Admin\AdminResource;
use App\Services\ActivityLogger;
use Astrotomic\Translatable\Contracts\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use LogicException;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Base des contrôleurs CRUD de l'administration.
 *
 * Chaque contrôleur concret déclare seulement son modèle, son formulaire et ses relations ;
 * la liste (pagination, filtres, tris), le détail, la création, la modification et la suppression
 * sont communs. Une suppression bloquée par la base (contenu encore utilisé) ou par une règle
 * métier (page système) renvoie 409 avec un message explicite.
 */
abstract class AdminCrudController extends Controller
{
    private const MAX_PER_PAGE = 100;

    /**
     * @return class-string<Model>
     */
    abstract protected function model(): string;

    /**
     * Formulaire de création et de modification. Un contenu en lecture seule (journal, abonnés…)
     * n'en déclare pas : ses routes n'exposent ni création ni modification.
     *
     * @return class-string<AdminFormRequest>
     */
    protected function formRequest(): string
    {
        throw new LogicException('Ce contenu est en lecture seule.');
    }

    /**
     * Relations plusieurs-à-plusieurs synchronisées depuis le formulaire (clé => relation).
     *
     * @return array<string, string>
     */
    protected function relations(): array
    {
        return [];
    }

    /**
     * Éléments enfants remplacés intégralement depuis le formulaire (clé => relation).
     *
     * @return array<string, string>
     */
    protected function children(): array
    {
        return [];
    }

    /**
     * Champs fixés par le serveur avant l'enregistrement (non assignables par le formulaire).
     *
     * @param  array<string, mixed>  $data
     */
    protected function beforeSave(Model $model, array $data, bool $isNew): void
    {
        //
    }

    /**
     * Relations chargées pour l'affichage détaillé.
     *
     * @return list<string>
     */
    protected function detailRelations(): array
    {
        return [...array_values($this->relations()), ...array_map(fn (string $relation): string => "{$relation}.translations", array_values($this->children()))];
    }

    /**
     * @return list<AllowedFilter|string>
     */
    protected function filters(): array
    {
        return [];
    }

    /**
     * @return list<string>
     */
    protected function sorts(): array
    {
        return ['id', 'created_at', 'updated_at'];
    }

    protected function defaultSort(): string
    {
        return '-id';
    }

    /**
     * Ressource JSON qui représente le contenu (AdminResource par défaut).
     *
     * @return class-string<AdminResource>
     */
    protected function resourceClass(): string
    {
        return AdminResource::class;
    }

    protected function resource(Model $model): JsonResource
    {
        $resourceClass = $this->resourceClass();

        return new $resourceClass($model);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for($this->baseQuery(), $request)
            ->allowedFilters(...$this->filters())
            ->allowedSorts(...$this->sorts())
            ->defaultSort($this->defaultSort());

        $perPage = min(max($request->integer('per_page', 20), 1), self::MAX_PER_PAGE);

        return $this->resourceClass()::collection($query->paginate($perPage)->withQueryString());
    }

    public function show(int $id): JsonResource
    {
        return $this->resource($this->find($id)->load($this->detailRelations()));
    }

    public function store(SaveModel $saveModel): JsonResponse
    {
        $data = $this->validated();
        $modelClass = $this->model();

        $model = $saveModel->handle(new $modelClass, $data, $this->relations(), $this->children(), $this->beforeSave(...));

        return $this->resource($this->find((int) $model->getKey())->load($this->detailRelations()))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(int $id, SaveModel $saveModel): JsonResource
    {
        $data = $this->validated();

        $saveModel->handle($this->find($id), $data, $this->relations(), $this->children(), $this->beforeSave(...));

        return $this->resource($this->find($id)->load($this->detailRelations()));
    }

    public function destroy(int $id, ActivityLogger $activityLogger): Response|JsonResponse
    {
        $model = $this->find($id);

        return $this->deleteOrConflict(fn () => $model->delete())
            ?? $this->deleted($model, $activityLogger, null);
    }

    /**
     * Restaure un contenu supprimé en douceur ; 404 pour un type sans corbeille.
     */
    public function restore(int $id, ActivityLogger $activityLogger): JsonResource
    {
        $model = $this->find($id);
        abort_unless($this->usesSoftDeletes() && method_exists($model, 'restore'), Response::HTTP_NOT_FOUND);

        $model->restore();
        $activityLogger->log(ActivityEvent::Updated, $model, ['deleted_at']);

        return $this->resource($this->find($id)->load($this->detailRelations()));
    }

    /**
     * Supprime définitivement un contenu (libère son slug, D-06) ; ses références polymorphes
     * sont nettoyées par ses traits (D-18). 404 pour un type sans corbeille.
     */
    public function forceDestroy(int $id, ActivityLogger $activityLogger): Response|JsonResponse
    {
        $model = $this->find($id);
        abort_unless($this->usesSoftDeletes(), Response::HTTP_NOT_FOUND);

        return $this->deleteOrConflict(fn () => $model->forceDelete())
            ?? $this->deleted($model, $activityLogger, 'suppression définitive');
    }

    /**
     * Exécute une suppression ; renvoie une réponse 409 si elle est bloquée par une règle métier
     * (page système) ou par la base (contenu encore référencé), null si elle a réussi.
     * La suppression et le nettoyage des références polymorphes (D-18) forment une seule transaction.
     *
     * @param  callable(): mixed  $delete
     */
    protected function deleteOrConflict(callable $delete): ?JsonResponse
    {
        try {
            DB::transaction($delete(...));
        } catch (LogicException $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_CONFLICT);
        } catch (QueryException $exception) {
            if (! in_array($exception->getCode(), ['23503', '23001'], true)) {
                throw $exception;
            }

            return response()->json(['message' => __('Ce contenu est encore utilisé ailleurs et ne peut pas être supprimé.')], Response::HTTP_CONFLICT);
        }

        return null;
    }

    /**
     * Indique si le type de contenu dispose d'une corbeille (suppression douce).
     */
    private function usesSoftDeletes(): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($this->model()), true);
    }

    protected function deleted(Model $model, ActivityLogger $activityLogger, ?string $description): Response
    {
        $activityLogger->log(ActivityEvent::Deleted, $model, description: $description);

        return response()->noContent();
    }

    /**
     * Requête de base de la liste (traductions chargées pour éviter les requêtes N+1).
     *
     * @return Builder<Model>
     */
    protected function baseQuery(): Builder
    {
        $modelClass = $this->model();
        $query = $modelClass::query();

        if (is_subclass_of($modelClass, Translatable::class)) {
            $query->with('translations');
        }

        return $query;
    }

    protected function find(int $id): Model
    {
        $modelClass = $this->model();
        $query = $modelClass::query();

        if ($this->usesSoftDeletes()) {
            $query->withoutGlobalScope(SoftDeletingScope::class);
        }

        if (is_subclass_of($modelClass, Translatable::class)) {
            $query->with('translations');
        }

        return $query->findOrFail($id);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(): array
    {
        /** @var AdminFormRequest $request */
        $request = app($this->formRequest());

        return $request->validated();
    }
}
