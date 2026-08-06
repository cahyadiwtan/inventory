<?php

namespace App\Livewire\Master;

use App\Models\Concerns\UsesUuid;
use App\Services\ActivityLogService;
use App\Support\MasterEntityConfig;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class MasterCrud extends Component
{
    use WithPagination;

    public string $entity;

    public array $form = [];

    public ?string $editingId = null;

    public bool $showModal = false;

    public string $search = '';

    public function mount(string $entity): void
    {
        $this->entity = $entity;
        $this->resetForm();
    }

    public function render()
    {
        $config = MasterEntityConfig::config($this->entity);
        $model = $config['model'];

        $query = $model::query();
        if ($this->search) {
            $query->where(function (Builder $q) {
                $q->where('code', 'like', "%{$this->search}%")
                    ->orWhere('name', 'like', "%{$this->search}%");
            });
        }

        $items = $query->orderByDesc('created_at')->paginate(10);

        return view('livewire.master.master-crud', [
            'config' => $config,
            'items' => $items,
        ])->title("{$config['title']} | Inventory System");
    }

    public function resetForm(): void
    {
        $config = MasterEntityConfig::config($this->entity);
        $defaults = [];

        foreach ($config['fields'] as $field => $meta) {
            $defaults[$field] = $meta['default'] ?? null;
        }

        $this->form = $defaults;
        $this->editingId = null;
        $this->showModal = false;
    }

    public function rules(): array
    {
        $config = MasterEntityConfig::config($this->entity);
        $table = (new $config['model']())->getTable();

        $rules = [];
        foreach ($config['fields'] as $field => $meta) {
            $rule = [$meta['required'] ? 'required' : 'nullable'];

            if ($field === 'code') {
                $rule[] = 'string';
                $rule[] = 'max:50';
                $rule[] = "unique:{$table},code,{$this->editingId},id";
            } elseif ($meta['type'] === 'number') {
                $rule[] = 'numeric';
                $rule[] = 'min:0';
                if ($meta['integer'] ?? false) {
                    $rule[] = 'integer';
                }
            } elseif ($meta['type'] === 'email') {
                $rule[] = 'email';
                $rule[] = 'max:100';
            } elseif ($field === 'rate') {
                $rule[] = 'numeric';
                $rule[] = 'min:0';
                $rule[] = 'max:100';
            } else {
                $rule[] = 'string';
                $rule[] = 'max:150';
            }

            $rules["form.{$field}"] = $rule;
        }

        return $rules;
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(string $id): void
    {
        $config = MasterEntityConfig::config($this->entity);
        $item = ($config['model'])::findOrFail($id);

        $this->form = $item->only(array_keys($config['fields']));
        $this->editingId = $id;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $config = MasterEntityConfig::config($this->entity);
        $data = $this->form;

        foreach ($config['fields'] as $field => $meta) {
            if (($meta['type'] ?? null) === 'number' && isset($data[$field]) && $data[$field] !== '') {
                $data[$field] = ($meta['integer'] ?? false)
                    ? (int) $data[$field]
                    : (float) $data[$field];
            }

            if (($meta['type'] ?? null) !== 'number' && isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        $model = ($config['model'])::query();
        if ($this->editingId) {
            $record = $model->findOrFail($this->editingId);
            $record->update($data);
            $message = "{$config['title']} updated.";
            $event = 'update';
        } else {
            $record = $model->create($data);
            $message = "{$config['title']} created.";
            $event = 'create';
        }

        app(ActivityLogService::class)->log($event, $record, "{$event}d {$config['title']}: {$record->name}");

        session()->flash('status', $message);
        $this->resetForm();
    }

    public function delete(string $id): void
    {
        $config = MasterEntityConfig::config($this->entity);
        $record = ($config['model'])::findOrFail($id);

        app(ActivityLogService::class)->log('delete', $record, "deleted {$config['title']}: {$record->name}");
        $record->delete();

        session()->flash('status', "{$config['title']} deleted.");
    }

    public function toggleActive(string $id): void
    {
        $config = MasterEntityConfig::config($this->entity);
        $record = ($config['model'])::findOrFail($id);
        $record->update(['is_active' => ! $record->is_active]);
    }
}
