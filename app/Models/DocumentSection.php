<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $chapter
 * @property ?string $subchapter
 * @property string $content
 * @property ?array $tags
 */
class DocumentSection extends Model
{
    use HasUuids;

    protected $fillable = ['chapter', 'subchapter', 'content', 'tags'];

    public $casts = ['tags' => 'json'];
}
