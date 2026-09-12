<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'role_name',
        'description',
        'can_manage_users',
        'can_manage_settings',
        'can_manage_asrama',
        'can_manage_kafetaria',
        'can_manage_kelas',
        'can_manage_absensi',
        'can_manage_nilai',
        'can_manage_administrasi',
        'can_assign_student_room',
        'can_manage_dorm_rooms',
        'can_manage_keys',
        'can_verify_payments',
        'can_verify_documents',
        'can_manage_cafeteria_menu',
        'can_scan_meal_card',
        'can_access_student_dashboard',
        'can_choose_dorm_room',
        'can_view_grades',
        'can_input_attendance',
        'can_manage_classes',
        'can_input_grades',
    ];

    protected $casts = [
        'can_manage_users' => 'boolean',
        'can_manage_settings' => 'boolean',
        'can_manage_asrama' => 'boolean',
        'can_manage_kafetaria' => 'boolean',
        'can_manage_kelas' => 'boolean',
        'can_manage_absensi' => 'boolean',
        'can_manage_nilai' => 'boolean',
        'can_manage_administrasi' => 'boolean',
        'can_assign_student_room' => 'boolean',
        'can_manage_dorm_rooms' => 'boolean',
        'can_manage_keys' => 'boolean',
        'can_verify_payments' => 'boolean',
        'can_verify_documents' => 'boolean',
        'can_manage_cafeteria_menu' => 'boolean',
        'can_scan_meal_card' => 'boolean',
        'can_access_student_dashboard' => 'boolean',
        'can_choose_dorm_room' => 'boolean',
        'can_view_grades' => 'boolean',
        'can_input_attendance' => 'boolean',
        'can_manage_classes' => 'boolean',
        'can_input_grades' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    /**
     * Check if role has specific permission
     */
    public function hasPermission(string $permission): bool
    {
        return $this->{$permission} ?? false;
    }

    /**
     * Check if role has any of the given permissions
     */
    public function hasPermissionAmong(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }
}
