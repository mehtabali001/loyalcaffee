<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MediaUploader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Generic, schema-light CRUD for CMS resources. Subclasses declare the model, validation
 * and which columns are media; translatable JSON fields are handled automatically.
 */
abstract class CrudController extends Controller
{
    /** @var class-string<Model> */
    protected string $model;

    protected array $with = [];

    /** Plain or JSON columns searched with ?q= */
    protected array $searchable = [];

    /** Columns holding uploaded file paths (old files are removed when replaced). */
    protected array $media = ['image'];

    /** Exact-match filters allowed from the query string. */
    protected array $filters = [];

    protected string $defaultSort = 'sort_order';

    protected ?int $perPage = null; // null = return everything (small, sortable lists)

    public function __construct(protected MediaUploader $uploader) {}

    abstract protected function rules(Request $request, ?Model $record): array;

    protected function query(Request $request): Builder
    {
        $q = $this->model::query()->with($this->with);

        if ($term = trim((string) $request->query('q'))) {
            $q->where(function (Builder $w) use ($term) {
                foreach ($this->searchable as $col) {
                    $w->orWhere($col, 'like', '%'.$term.'%');
                }
            });
        }
        foreach ($this->filters as $f) {
            if ($request->filled($f)) {
                $q->where($f, $request->query($f));
            }
        }

        return $this->applySort($q);
    }

    protected function applySort(Builder $q): Builder
    {
        return $this->defaultSort === 'sort_order'
            ? $q->orderBy('sort_order')->orderBy('id')
            : $q->orderByDesc($this->defaultSort);
    }

    public function index(Request $request): JsonResponse
    {
        $q = $this->query($request);

        if ($this->perPage) {
            $page = $q->paginate($request->integer('per_page', $this->perPage));
            $page->getCollection()->transform(fn ($m) => $this->present($m));

            return response()->json($page);
        }

        return response()->json(['data' => $q->get()->map(fn ($m) => $this->present($m))]);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->present($this->find($id)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules($request, null));
        $record = new $this->model;
        $this->persist($record, $data);

        return response()->json($this->present($record->fresh($this->with)), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $record = $this->find($id);
        $data = $request->validate($this->rules($request, $record));
        $this->persist($record, $data);

        return response()->json($this->present($record->fresh($this->with)));
    }

    public function destroy(int $id): JsonResponse
    {
        $record = $this->find($id);
        $paths = array_filter(array_map(fn ($c) => $record->getAttribute($c), $this->media));
        $record->delete();
        foreach ($paths as $p) {
            $this->uploader->delete($p);
        }

        return response()->json(['ok' => true]);
    }

    /** PATCH {ids: [3,1,2]} — drag & drop ordering. */
    public function reorder(Request $request): JsonResponse
    {
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']])['ids'];
        DB::transaction(function () use ($ids) {
            foreach ($ids as $i => $id) {
                $this->model::whereKey($id)->update(['sort_order' => $i + 1]);
            }
        });
        \App\Support\ContentVersion::bump(); // mass update skips model events

        return response()->json(['ok' => true]);
    }

    /** PATCH {field: "is_active"} — quick toggles from list views. */
    public function toggle(Request $request, int $id): JsonResponse
    {
        $field = $request->validate(['field' => ['required', 'string']])['field'];
        abort_unless(in_array($field, ['is_active', 'is_featured', 'is_available', 'reservations_open', 'is_big_match'], true), 422);
        $record = $this->find($id);
        abort_unless(in_array($field, $record->getFillable(), true), 422);
        $record->update([$field => ! $record->getAttribute($field)]);

        return response()->json($this->present($record));
    }

    protected function find(int $id): Model
    {
        return $this->model::query()->with($this->with)->findOrFail($id);
    }

    protected function persist(Model $record, array $data): void
    {
        $replaced = [];
        foreach ($this->media as $col) {
            $old = $record->exists ? $record->getAttribute($col) : null;
            if ($old && array_key_exists($col, $data) && $data[$col] !== $old) {
                $replaced[] = $old;
            }
        }

        DB::transaction(function () use ($record, $data) {
            $record->fill($data);
            if (method_exists($record, 'fillTranslations')) {
                $record->fillTranslations($data);
            }
            $record->save();
            $this->afterPersist($record, $data);
        });

        // Only remove old files once the new reference is safely committed
        foreach ($replaced as $path) {
            $this->uploader->delete($path);
        }
    }

    /** Hook for relations (e.g. syncing children). */
    protected function afterPersist(Model $record, array $data): void {}

    /** Admin representation: raw translatable blobs + resolved media URLs for previews. */
    protected function present(Model $m): array
    {
        $arr = $m->toArray();
        foreach ($this->media as $col) {
            $arr[$col.'_url'] = media_url($m->getAttribute($col));
        }

        return $arr;
    }

    /** Validation helper for {en, ar} fields. The default locale is required when $required. */
    protected function trans(string $field, bool $required = false, int $max = 255): array
    {
        $default = config('loyal.locales')[0];
        $rules = [$field => [$required ? 'required' : 'nullable', 'array']];
        foreach (config('loyal.locales') as $l) {
            $rules["$field.$l"] = [($required && $l === $default) ? 'required' : 'nullable', 'string', "max:$max"];
        }

        return $rules;
    }
}
