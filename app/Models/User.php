<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, \Illuminate\Database\Eloquent\SoftDeletes;

    protected $primaryKey = 'sk_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'sk_user',
        'employee_nik',
        'employee_name',
        'employee_email',
        'email_verified_at',
        'password',
        'remember_token',
        'is_active',
        'level',
        'join_date',
        'end_date',
        'create_by',
        'create_date',
        'modified_by',
        'modified_date',
        'name',
        'email',
        'deleted_at',
    ];

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'sk_user';
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getEmailForPasswordReset()
    {
        return $this->employee_email;
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getEmailForVerification()
    {
        return $this->employee_email;
    }

    /**
     * Get the name attribute for authentication.
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->employee_name;
    }

    /**
     * Get the email attribute for authentication.
     *
     * @return string
     */
    public function getEmailAttribute()
    {
        return $this->employee_email;
    }

    /**
     * Set the name attribute.
     *
     * @param  string  $value
     * @return void
     */
    public function setNameAttribute($value)
    {
        $this->attributes['employee_name'] = $value;
    }

    /**
     * Set the email attribute.
     *
     * @param  string  $value
     * @return void
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['employee_email'] = $value;
    }

    /**
     * Set the is_active attribute.
     *
     * @param  string|null  $value
     * @return void
     */
    public function setIsActiveAttribute($value)
    {
        // If value is null or empty, set to default 'Active'
        $this->attributes['is_active'] = $value ?: 'Active';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'create_date' => 'datetime',
            'modified_date' => 'datetime',
            // 'is_active' => 'boolean', // Using enum string instead
        ];
    }

    /**
     * Reset the password to NIK.
     *
     * @param  User  $user
     * @return void
     */
    public function resetPasswordToNik(User $user)
    {
        $user->password = Hash::make($user->employee_nik);
        $user->save();

        // Beri notifikasi sukses
    }

    protected static ?string $recordTitleAttribute = 'employee_name';

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->is_active)) {
                $user->is_active = 'Active';
            }
        });
    }
}
