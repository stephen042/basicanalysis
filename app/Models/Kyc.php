<?php

namespace App\Models;

use App\Support\KycDocumentPath;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kyc extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function storedDocumentPath(string $side): ?string
    {
        if (!in_array($side, ['front', 'back'], true)) {
            return null;
        }

        $value = $side === 'front' ? $this->frontimg : $this->backimg;

        return $value ?: null;
    }

    /**
     * Whether the document referenced in the database can actually be found.
     * This lets the admin page degrade gracefully instead of rendering a
     * broken image (and a 404 request) for a missing file.
     */
    public function hasDocument(string $side): bool
    {
        $path = $this->storedDocumentPath($side);

        return $path !== null && KycDocumentPath::exists($path);
    }

    public function documentUrl(string $side): ?string
    {
        if (!$this->hasDocument($side)) {
            return null;
        }

        return route('admin.kyc.image', [$this->getKey(), $side]);
    }
}
