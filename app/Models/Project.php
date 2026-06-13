<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = ['未対応', '検討中', '提案済', '成約', '見送り'];

    protected $fillable = [
        'user_id',
        'client_id',
        'case_name',
        'work_content',
        'location',
        'period',
        'unit_price',
        'settlement',
        'interview_count',
        'flow_limit',
        'contract_type',
        'age_limit',
        'foreigner_ok',
        'freelance_ok',
        'memo',
        'status',
        'raw_text',
        'raw_text_hash',
    ];

    protected $casts = [
        'interview_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'project_skills')
            ->withPivot('requirement')
            ->withTimestamps();
    }

    public function requiredSkills(): BelongsToMany
    {
        return $this->skills()->wherePivot('requirement', 'required');
    }

    public function preferredSkills(): BelongsToMany
    {
        return $this->skills()->wherePivot('requirement', 'preferred');
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (!filled($keyword)) {
            return $query;
        }
        $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $keyword) . '%';
        return $query->where(function (Builder $q) use ($like) {
            $q->where('case_name', 'like', $like)
                ->orWhere('work_content', 'like', $like)
                ->orWhere('location', 'like', $like)
                ->orWhereHas('skills', fn ($sq) => $sq->where('name', 'like', $like));
        });
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if (!filled($status)) {
            return $query;
        }
        return $query->where('status', $status);
    }
}
