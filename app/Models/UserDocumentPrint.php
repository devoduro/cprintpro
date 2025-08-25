<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDocumentPrint extends Model
{
    protected $fillable = [
        'user_id',
        'document_id',
        'print_count',
        'last_printed_at'
    ];

    protected $casts = [
        'last_printed_at' => 'datetime',
        'print_count' => 'integer'
    ];

    /**
     * Get the user that owns the print record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the document that was printed.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Increment the print count for this user-document combination.
     */
    public function incrementPrintCount(): void
    {
        $this->increment('print_count');
        $this->update(['last_printed_at' => now()]);
    }
}
