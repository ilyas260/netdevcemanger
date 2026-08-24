<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Création des rôles
        $adminRole = Role::create(['name' => 'admin']);
        $techRole = Role::create(['name' => 'technicien']);
        $consultantRole = Role::create(['name' => 'consultant']);

        // Création de l'utilisateur admin par défaut
        $admin = User::create([
            'name' => 'Administrateur Réseau',
            'email' => 'admin@netdevice.local',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $admin->assignRole($adminRole);

        // Utilisateurs de test optionnels
        $tech = User::create([
            'name' => 'Technicien Support',
            'email' => 'tech@netdevice.local',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $tech->assignRole($techRole);
    }
}
