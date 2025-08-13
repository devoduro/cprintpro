<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'document_category_id',
        'uploaded_by',
        'is_printable',
        'is_active',
        'print_count',
        'last_printed_at',
        'print_settings'
    ];

    protected $casts = [
        'is_printable' => 'boolean',
        'is_active' => 'boolean',
        'print_count' => 'integer',
        'file_size' => 'integer',
        'last_printed_at' => 'datetime',
        'print_settings' => 'array'
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(DocumentCategory::class, 'document_category_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrintable($query)
    {
        return $query->where('is_printable', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('document_category_id', $categoryId);
    }

    // Accessors
    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFileUrlAttribute()
    {
        return Storage::url($this->file_path);
    }

    // Methods
    public function incrementPrintCount()
    {
        $this->increment('print_count');
        $this->update(['last_printed_at' => now()]);
    }

    public function canBePrinted()
    {
        return $this->is_active && $this->is_printable;
    }
}
