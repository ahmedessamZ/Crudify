<?php

namespace App\Models\Admin;

use App\Enums\AdminTypes;
use App\Foundation\Enums\ActivationType;
use App\Foundation\Models\BaseModel;
use App\Modules\BaseModule\Database\factories\BaseModuleFactory;
use App\Modules\BaseModule\Filters\BaseModuleFilters;
use Database\Factories\AdminFactory;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends BaseModel implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use HasFactory, Notifiable, HasRoles;
    use Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail;

    protected $table = 'admins';

    /**
     * @var string
     */
    protected string $guard = 'admin';

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return AdminFactory::new();
    }

    /**
     * @var array
     */
    protected $fillable = [
        'name', 'password', 'email', 'primary_admin', 'status', 'email_verified_at'
    ];

    /**
     * @var string[]
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Apply all relevant Sizes filters
     *
     * @param Builder $query
     * @param BaseModuleFilters $filters
     * @return Builder
     */
    public function scopeFilter(Builder $query, BaseModuleFilters $filters): Builder
    {
        return $filters->apply($query);
    }

    /**
     * encrypt password before save
     *
     * @param string|null $value
     * @return void
     */
    public function setPasswordAttribute(?string $value): void
    {
        if ($value != null) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopeActive(Builder $builder): Builder
    {
        return $builder->where('status', ActivationType::ACTIVE->getActivationStatus());
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopeInActive(Builder $builder): Builder
    {
        return $builder->where('status', ActivationType::IN_ACTIVE->getActivationStatus());
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopeNotPrimary(Builder $builder): Builder
    {
        return $builder->where('primary_admin', AdminTypes::ADMIN->value);
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopePrimary(Builder $builder): Builder
    {
        return $builder->where('primary_admin', AdminTypes::PRIMARY_ADMIN->value);
    }

    /**
     * @param $collection_name
     * @return string|null
     */
    public function getMedia($collection_name): ?string
    {

        $collection = collect($this->media?->where('collection_name', $collection_name))->sortByDesc('id')->first();

        return optional($collection)->file ?
            mediaBaseURL() . "/admins/$collection_name/" . optional($collection)->file : asset('media/logos/logo-icon.png');
    }

    /**
     * @return HasOne
     */
    public function media(): HasOne
    {
        return $this->hasOne(AdminMedia::class);
    }

}
