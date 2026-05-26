<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'no_hp',
        'email',
        'password',
        'status',
        'role',
    ];

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'user_clients', 'user_id', 'client_id');
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
        ];
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'user_has_menu', 'user_id', 'menu_id');
    }

    public function hasMenu($menuName)
    {
        return $this->menus()->where('name', $menuName)->exists();
    }

    public function isAdminWMS()
    {
        return $this->role === 'Admin WMS';
    }

    public function isClientUser()
    {
        return $this->role === 'Client User';
    }

    /**
     * Get IDs of clients this user can access
     */
    public function getAccessibleClientIds()
    {
        if ($this->isAdminWMS()) {
            return Client::pluck('id')->toArray();
        }

        $assignedIds = $this->clients()->pluck('client_id')->toArray();

        // If Client User has no specific client assignments, they can see all clients
        return !empty($assignedIds) ? $assignedIds : Client::pluck('id')->toArray();
    }

    /**
     * Get client list for dropdown (all if admin or no assignments, filtered otherwise)
     */
    public function getAvailableClients()
    {
        if ($this->isAdminWMS()) {
            return Client::all();
        }

        $assignedClients = $this->clients;
        return $assignedClients->isNotEmpty() ? $assignedClients : Client::all();
    }
}
