<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'nip',
        'nisn',
        'nis',
        'gender',
        'birth_place',
        'birth_date',
        'student_class',
        'dapodik_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date' => 'date',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (self $user) {
            if ($user->wasChanged(['student_class', 'gender']) || $user->wasRecentlyCreated) {
                $oldClass = $user->getOriginal('student_class');
                if ($oldClass && preg_match('/^[789][A-Z]$/i', (string) $oldClass)) {
                    self::syncSingleRombelStatistic(strtoupper((string) $oldClass));
                }
                if ($user->student_class && preg_match('/^[789][A-Z]$/i', (string) $user->student_class)) {
                    self::syncSingleRombelStatistic(strtoupper((string) $user->student_class));
                }
            }
        });

        static::deleted(function (self $user) {
            if ($user->student_class && preg_match('/^[789][A-Z]$/i', (string) $user->student_class)) {
                self::syncSingleRombelStatistic(strtoupper((string) $user->student_class));
            }
        });
    }

    public static function syncSingleRombelStatistic(string $className, string $academicYear = '2026/2027'): void
    {
        if (! preg_match('/^([789])[A-Z]$/i', $className, $m)) {
            return;
        }

        $male = self::where('student_class', $className)->where('gender', 'L')->count();
        $female = self::where('student_class', $className)->where('gender', 'P')->count();

        if ($male === 0 && $female === 0) {
            return;
        }

        StudentStatistic::updateOrCreate(
            [
                'academic_year' => $academicYear,
                'class_name' => strtoupper($className),
            ],
            [
                'grade_level' => (int) $m[1],
                'male_count' => $male,
                'female_count' => $female,
                'total_count' => $male + $female,
            ]
        );
    }

    public static function syncAllRombelStatistics(string $academicYear = '2026/2027'): int
    {
        $classes = [
            '7A', '7B', '7C', '7D', '7E', '7F', '7G',
            '8A', '8B', '8C', '8D', '8E', '8F', '8G',
            '9A', '9B', '9C', '9D', '9E', '9F',
        ];

        $synced = 0;
        foreach ($classes as $cls) {
            self::syncSingleRombelStatistic($cls, $academicYear);
            $synced++;
        }

        return $synced;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['super_admin', 'guru', 'staf_perpus', 'panitia_pilketos']);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    public function learningMaterials(): HasMany
    {
        return $this->hasMany(LearningMaterial::class, 'created_by');
    }

    public function questionBanks(): HasMany
    {
        return $this->hasMany(QuestionBank::class, 'created_by');
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'user_id');
    }
}
