<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Book
 *
 * @property int $id
 * @property int $region_id
 * @property string|null $front_page
 * @property string|null $name
 * @property string|null $circle_audio
 * @property string|null $pencil_audio
 * @property string|null $planet_image
 * @property string|null $face_video
 * @property string|null $eye_image
 * @property string|null $tv_video
 * @property string|null $message_image
 * @property string|null $message_text
 * @property string|null $diamond_image
 * @property string|null $diamond_text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Region $region
 * @property-read \Illuminate\Database\Eloquent\Collection|Experience[] $experiences
 * @property-read \Illuminate\Database\Eloquent\Collection|Evidence[] $evidences
 */
class Book extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'books';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'region_id',
        'front_page',
        'name',
        'circle_audio',
        'pencil_audio',
        'planet_image',
        'face_video',
        'eye_image',
        'tv_video',
        'message_image',
        'message_text',
        'diamond_image',
        'diamond_text',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'region_id' => 'integer',
    ];

    /**
     * Region associated with this book.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    /**
     * Pedagogical experiences created for this book.
     */
    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class, 'book_id');
    }

    /**
     * Student learning evidences associated with this book.
     */
    public function evidences(): HasMany
    {
        return $this->hasMany(Evidence::class, 'book_id');
    }
}
