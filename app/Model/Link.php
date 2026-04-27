<?php

declare(strict_types=1);

namespace App\Model;

use App\Domain\Link\LinkStatus;
use Hyperf\Database\Model\Concerns\HasUuids;
use Hyperf\DbConnection\Model\Model;

/**
 * @property string $id 
 * @property string $slug 
 * @property string $original_url 
 * @property string $status 
 * @property string $expires_at 
 * @property int $clicks
 * @property string $owner_user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Link extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'links';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        "id",
        "slug",
        "clicks",
        "original_url",
        "status",
        "expires_at",
        "owner_user_id"
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['created_at' => 'datetime', 'updated_at' => 'datetime'];

    protected array $attributes = [
        'status' => LinkStatus::ACTIVE->value,
    ];
}
