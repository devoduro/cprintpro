<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'color',
        'icon',
        'is_active',
        'sort_order',
        'parent_id',
        'type',
        'path',
        'depth',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    // Relationships
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function activeDocuments()
    {
        return $this->hasMany(Document::class)->where('is_active', true);
    }

    // Hierarchical relationships
    public function parent()
    {
        return $this->belongsTo(DocumentCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(DocumentCategory::class, 'parent_id');
    }

    public function activeChildren()
    {
        return $this->hasMany(DocumentCategory::class, 'parent_id')->where('is_active', true);
    }

    public function descendants()
    {
        return $this->hasMany(DocumentCategory::class, 'parent_id')->with('descendants');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeRootCategories($query)
    {
        return $query->whereNull('parent_id')->where('type', 'category');
    }

    public function scopeFolders($query)
    {
        return $query->where('type', 'folder');
    }

    public function scopeCategories($query)
    {
        return $query->where('type', 'category');
    }

    // Accessors
    public function getDocumentCountAttribute()
    {
        return $this->documents()->count();
    }

    public function getActiveDocumentCountAttribute()
    {
        return $this->activeDocuments()->count();
    }

    public function getTotalDocumentCountAttribute()
    {
        $count = $this->documents()->count();
        foreach ($this->children as $child) {
            $count += $child->total_document_count;
        }
        return $count;
    }

    public function getBreadcrumbsAttribute()
    {
        $breadcrumbs = collect();
        $current = $this;
        
        while ($current) {
            $breadcrumbs->prepend($current);
            $current = $current->parent;
        }
        
        return $breadcrumbs;
    }

    // Methods
    public function isFolder()
    {
        return $this->type === 'folder';
    }

    public function isCategory()
    {
        return $this->type === 'category';
    }

    public function hasChildren()
    {
        return $this->children()->count() > 0;
    }

    public function updatePath()
    {
        if ($this->parent) {
            $this->path = $this->parent->path ? $this->parent->path . '/' . $this->name : $this->name;
            $this->depth = $this->parent->depth + 1;
        } else {
            $this->path = $this->name;
            $this->depth = 0;
        }
        $this->save();

        // Update children paths
        foreach ($this->children as $child) {
            $child->updatePath();
        }
    }
}
