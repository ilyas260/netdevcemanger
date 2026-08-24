<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UserManager extends Component
{
    use WithPagination;

    // Password modal state
    public $showPasswordModal = false;
    public $editingUserId = null;
    public $editingUserName = '';
    public $new_password = '';
    public $new_password_confirmation = '';

    public function mount()
    {
        // Safety check, should be protected by middleware but just in case
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }
    }

    public function updateRole($userId, $roleName)
    {
        $user = User::findOrFail($userId);
        
        // Prevent admin from removing their own admin role accidentally
        if ($user->id === auth()->id() && $roleName !== 'admin') {
            session()->flash('error', "Vous ne pouvez pas retirer vos propres droits d'administrateur.");
            return;
        }

        // Validate role exists
        if (!Role::where('name', $roleName)->exists()) {
            session()->flash('error', "Ce rôle n'existe pas.");
            return;
        }

        // Update role
        $user->syncRoles([$roleName]);
        session()->flash('success', "Le rôle de {$user->name} a été mis à jour.");
    }

    public function openPasswordModal($userId)
    {
        $user = User::findOrFail($userId);
        $this->editingUserId = $user->id;
        $this->editingUserName = $user->name;
        $this->new_password = '';
        $this->new_password_confirmation = '';
        $this->showPasswordModal = true;
    }

    public function closePasswordModal()
    {
        $this->showPasswordModal = false;
        $this->editingUserId = null;
        $this->editingUserName = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';
        $this->resetValidation();
    }

    public function updatePassword()
    {
        $this->validate([
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'new_password.required' => 'Le mot de passe est requis.',
            'new_password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'new_password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $user = User::findOrFail($this->editingUserId);
        $user->update([
            'password' => Hash::make($this->new_password)
        ]);

        session()->flash('success', "Le mot de passe de {$user->name} a été modifié avec succès.");
        $this->closePasswordModal();
    }

    public function render()
    {
        $users = User::with('roles')->orderBy('name')->paginate(15);
        $roles = Role::pluck('name')->toArray();

        return view('livewire.user-manager', [
            'users' => $users,
            'roles' => $roles
        ])->layout('layouts.app', ['title' => 'Gestion des Utilisateurs', 'header' => 'Administration des accès']);
    }
}
