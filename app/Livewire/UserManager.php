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

    // Create user modal
    public bool $showCreateModal = false;
    public string $create_name = '';
    public string $create_email = '';
    public string $create_role = 'technicien';
    public string $create_password = '';
    public string $create_password_confirmation = '';

    // Password modal state
    public bool $showPasswordModal = false;
    public ?int $editingUserId = null;
    public string $editingUserName = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    // Delete modal
    public bool $showDeleteModal = false;
    public ?int $deletingUserId = null;
    public string $deletingUserName = '';

    public function mount()
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }
    }

    // ---------- Create User ----------
    public function openCreateModal()
    {
        $this->reset(['create_name', 'create_email', 'create_role', 'create_password', 'create_password_confirmation']);
        $this->create_role = 'technicien';
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    public function createUser()
    {
        $this->validate([
            'create_name'     => ['required', 'string', 'max:255'],
            'create_email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'create_role'     => ['required', 'string', 'exists:roles,name'],
            'create_password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'create_name.required'     => 'Le nom est obligatoire.',
            'create_email.required'    => "L'adresse email est obligatoire.",
            'create_email.email'       => "L'adresse email n'est pas valide.",
            'create_email.unique'      => 'Cette adresse email est déjà utilisée.',
            'create_role.exists'       => "Ce rôle n'existe pas.",
            'create_password.required' => 'Le mot de passe est obligatoire.',
            'create_password.confirmed'=> 'Les mots de passe ne correspondent pas.',
        ]);

        $user = User::create([
            'name'              => $this->create_name,
            'email'             => $this->create_email,
            'password'          => Hash::make($this->create_password),
            'email_verified_at' => now(),
        ]);

        $user->assignRole($this->create_role);

        session()->flash('success', "L'utilisateur {$user->name} a été créé avec le rôle " . ucfirst($this->create_role) . ".");
        $this->closeCreateModal();
    }

    // ---------- Update Role ----------
    public function updateRole($userId, $roleName)
    {
        $user = User::findOrFail($userId);

        if ($user->id === auth()->id() && $roleName !== 'admin') {
            session()->flash('error', "Vous ne pouvez pas retirer vos propres droits d'administrateur.");
            return;
        }

        if (!Role::where('name', $roleName)->exists()) {
            session()->flash('error', "Ce rôle n'existe pas.");
            return;
        }

        $user->syncRoles([$roleName]);
        session()->flash('success', "Le rôle de {$user->name} a été mis à jour en « " . ucfirst($roleName) . " ».");
    }

    // ---------- Password ----------
    public function openPasswordModal($userId)
    {
        $user = User::findOrFail($userId);
        $this->editingUserId   = $user->id;
        $this->editingUserName = $user->name;
        $this->new_password    = '';
        $this->new_password_confirmation = '';
        $this->showPasswordModal = true;
    }

    public function closePasswordModal()
    {
        $this->showPasswordModal = false;
        $this->editingUserId    = null;
        $this->editingUserName  = '';
        $this->new_password     = '';
        $this->new_password_confirmation = '';
        $this->resetValidation();
    }

    public function updatePassword()
    {
        $this->validate([
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'new_password.required'  => 'Le mot de passe est requis.',
            'new_password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'new_password.min'       => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $user = User::findOrFail($this->editingUserId);
        $user->update(['password' => Hash::make($this->new_password)]);

        session()->flash('success', "Le mot de passe de {$user->name} a été modifié avec succès.");
        $this->closePasswordModal();
    }

    // ---------- Delete ----------
    public function openDeleteModal($userId)
    {
        if ($userId === auth()->id()) {
            session()->flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            return;
        }
        $user = User::findOrFail($userId);
        $this->deletingUserId   = $user->id;
        $this->deletingUserName = $user->name;
        $this->showDeleteModal  = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal  = false;
        $this->deletingUserId   = null;
        $this->deletingUserName = '';
    }

    public function deleteUser()
    {
        if ($this->deletingUserId === auth()->id()) {
            session()->flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            $this->closeDeleteModal();
            return;
        }

        $user = User::findOrFail($this->deletingUserId);
        $name = $user->name;
        $user->delete();

        session()->flash('success', "L'utilisateur « {$name} » a été supprimé.");
        $this->closeDeleteModal();
    }

    public function render()
    {
        $users = User::with('roles')->orderBy('name')->paginate(15);
        $roles = Role::pluck('name')->toArray();

        return view('livewire.user-manager', [
            'users' => $users,
            'roles' => $roles,
        ])->layout('layouts.app', ['title' => 'Gestion des Utilisateurs', 'header' => 'Administration des accès']);
    }
}
