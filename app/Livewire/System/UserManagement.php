<?php

namespace App\Livewire\System;

use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UserManagement extends Component
{
    use WithPagination;

    public array $form = [
        'name' => '',
        'email' => '',
        'password' => '',
    ];

    /** @var list<string> */
    public array $roles = [];

    public ?string $editingId = null;

    public bool $showModal = false;

    public string $search = '';

    public function render()
    {
        $query = User::query()->with('roles');

        if ($this->search) {
            $query->where(function (Builder $q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        $items = $query->orderBy('name')->paginate(10);

        return view('livewire.system.user-management', [
            'items' => $items,
            'availableRoles' => Role::query()->orderBy('name')->get(),
        ])->title('User Management | Inventory System');
    }

    public function rules(): array
    {
        return [
            'form.name' => ['required', 'string', 'max:150'],
            'form.email' => ['required', 'email', 'max:150', "unique:users,email,{$this->editingId},id"],
            'form.password' => [$this->editingId ? 'nullable' : 'required', 'string', 'min:6'],
            'roles' => ['array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(string $id): void
    {
        $user = User::with('roles')->findOrFail($id);

        $this->form = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => '',
        ];
        $this->roles = $user->roles->pluck('name')->all();
        $this->editingId = $id;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->form['name'],
            'email' => $this->form['email'],
        ];

        if ($this->form['password'] !== '' && $this->form['password'] !== null) {
            $data['password'] = $this->form['password'];
        }

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $user->update($data);
            $message = 'User updated.';
            $event = 'update';
        } else {
            $user = User::create($data);
            $message = 'User created.';
            $event = 'create';
        }

        $user->syncRoles($this->roles);

        app(ActivityLogService::class)->log($event, $user, "{$event}d user: {$user->name}");

        session()->flash('status', $message);
        $this->resetForm();
    }

    public function delete(string $id): void
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            session()->flash('status', 'Tidak dapat menghapus akun sendiri.');

            return;
        }

        app(ActivityLogService::class)->log('delete', $user, "deleted user: {$user->name}");
        $user->delete();

        session()->flash('status', 'User deleted.');
    }

    public function toggleActive(string $id): void
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            session()->flash('status', 'Tidak dapat menonaktifkan akun sendiri.');

            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
    }

    public function resetForm(): void
    {
        $this->form = [
            'name' => '',
            'email' => '',
            'password' => '',
        ];
        $this->roles = [];
        $this->editingId = null;
        $this->showModal = false;
    }
}