<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    use HasRecursiveRelationships;

    protected static function booted(): void
    {
        static::creating(function (Category $cat) {
            if (! empty($cat->slug)) {
                return;
            }

            $base = Str::slug($cat->name);
            $slug = $base;
            $i = 1;
            while (self::where('slug', $slug)->exists()) {
                $slug = $base.'-'.$i++;
            }
            $cat->slug = $slug;
        });

        static::saving(function (Category $cat) {
            if ($cat->exists && $cat->isDirty('parent_id')) {
                self::guardAgainstCircularParent($cat->id, $cat->parent_id);
            }
        });

        static::saved(fn () => Cache::forget('categories.tree.flat.v1'));
        static::deleted(fn () => Cache::forget('categories.tree.flat.v1'));
    }

    protected $guarded = [];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    // Relations
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeLeaves(Builder $query): Builder
    {
        return $query->whereDoesntHave('children');
    }

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    public function isLeaf(): bool
    {
        return $this->relationLoaded('children')
            ? $this->children->isEmpty()
            : ! self::where('parent_id', $this->id)->exists();
    }

    public function isAncestorOf(Category $other): bool
    {
        $cursor = $other->parent;

        while ($cursor) {
            if ($cursor->id === $this->id) {
                return true;
            }
            $cursor = $cursor->parent;
        }

        return false;
    }

    public function isDescendantOf(Category $other): bool
    {
        return $other->isAncestorOf($this);
    }

    public static function loadTree(): Collection
    {
        $all = self::orderBy('name')->get();
        $byParent = $all->groupBy('parent_id');

        foreach ($all as $category) {
            $category->setRelation('children', $byParent->get($category->id, collect()));
        }

        $roots = $byParent->get(null, collect());
        self::assignDepth($roots, 0);

        return $roots;
    }

    protected static function assignDepth(Collection $nodes, int $depth): void
    {
        foreach ($nodes as $node) {
            $node->depth = $depth;
            self::assignDepth($node->children, $depth + 1);
        }
    }

    public function getBreadcrumbs(): Collection
    {
        return $this->ancestorsAndSelf()
            ->orderBy($this->getDepthName())
            ->get();
    }

    public function getDescendantIds(): array
    {
        return $this->descendantsAndSelf()
            ->pluck('id')
            ->all();
    }

    public static function rawDescendantIds(int $id): array
    {
        $rows = DB::select(
            'WITH RECURSIVE d AS (
                SELECT id FROM categories WHERE id = ?
                UNION ALL
                SELECT c.id FROM categories c INNER JOIN d ON c.parent_id = d.id
            ) SELECT id FROM d',
            [$id]
        );

        return array_map(static fn ($row) => (int) $row->id, $rows);
    }

    public static function guardAgainstCircularParent(int $movingId, ?int $newParentId): void
    {
        if ($newParentId === null) {
            return;
        }
        if ($newParentId === $movingId) {
            throw new DomainException('Category cannot be its own parent.');
        }

        $cursor = self::find($newParentId);
        while ($cursor) {
            if ($cursor->id === $movingId) {
                throw new DomainException('Circular hierarchy: new parent is a descendant.');
            }
            $cursor = $cursor->parent;
        }
    }

    // LocalScopes
    public function scopeWithProducts(Builder $query): Builder
    {
        return $query->has('products');
    }

    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }
}
