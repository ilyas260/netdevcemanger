<?php

namespace App\Livewire;

use App\Models\Setting;
use Livewire\Component;

class DiagnosticManager extends Component
{
    public array $diagnostics = [];
    public string $newLabel = '';
    public string $editingKey = '';
    public string $editingLabel = '';

    public function mount()
    {
        $this->loadDiagnostics();
    }

    protected function loadDiagnostics(): void
    {
        $stored = Setting::get('diagnostic_types');

        if ($stored) {
            $this->diagnostics = json_decode($stored, true) ?? [];
        } else {
            // Seed from the model constants on first load
            $this->diagnostics = \App\Models\ErrorLog::SOLUTION_TYPES;
            $this->saveToDB();
        }
    }

    protected function saveToDB(): void
    {
        Setting::set('diagnostic_types', json_encode($this->diagnostics), 'diagnostics');
    }

    public function addDiagnostic()
    {
        $this->validate(['newLabel' => 'required|string|min:3|max:100']);

        $key = \Illuminate\Support\Str::slug($this->newLabel, '_');
        // Ensure uniqueness
        $base = $key;
        $i = 2;
        while (array_key_exists($key, $this->diagnostics)) {
            $key = $base . '_' . $i++;
        }

        $this->diagnostics[$key] = $this->newLabel;
        $this->saveToDB();
        $this->newLabel = '';
        session()->flash('success', 'Diagnostic ajouté avec succès.');
    }

    public function startEdit(string $key)
    {
        $this->editingKey = $key;
        $this->editingLabel = $this->diagnostics[$key] ?? '';
    }

    public function saveEdit()
    {
        $this->validate(['editingLabel' => 'required|string|min:3|max:100']);

        if (isset($this->diagnostics[$this->editingKey])) {
            $this->diagnostics[$this->editingKey] = $this->editingLabel;
            $this->saveToDB();
            session()->flash('success', 'Diagnostic modifié.');
        }

        $this->editingKey = '';
        $this->editingLabel = '';
    }

    public function cancelEdit()
    {
        $this->editingKey = '';
        $this->editingLabel = '';
    }

    public function deleteDiagnostic(string $key)
    {
        unset($this->diagnostics[$key]);
        $this->saveToDB();
        session()->flash('success', 'Diagnostic supprimé.');
    }

    public function render()
    {
        return view('livewire.diagnostic-manager')
            ->layout('layouts.app', ['title' => 'Gestion des Diagnostics']);
    }
}
