<?php

namespace App\Livewire\Master;

use App\Models\Supplier;
use App\Services\ActivityLogService;
use Livewire\Component;

class SupplierFormComponent extends Component
{
    public array $form = [];

    public ?string $editingId = null;

    public function mount(?Supplier $supplier = null): void
    {
        $defaults = [
            'code' => null,
            'name' => null,
            'npwp' => null,
            'address' => null,
            'phone' => null,
            'email' => null,
            'pic_name' => null,
            'payment_term_days' => 30,
            'credit_limit' => 0,
        ];

        if ($supplier) {
            $this->form = $supplier->only(array_keys($defaults));
            $this->editingId = $supplier->id;
        } else {
            $this->form = $defaults;
        }

        $this->form['payment_term_days'] = ($this->form['payment_term_days'] ?? null) === null ? 30 : $this->form['payment_term_days'];
        $this->form['credit_limit'] = ($this->form['credit_limit'] ?? null) === null ? 0 : $this->form['credit_limit'];
    }

    public function rules(): array
    {
        $unique = "unique:suppliers,code,{$this->editingId},id";

        return [
            'form.code' => ['required', 'string', 'max:50', $unique],
            'form.name' => ['required', 'string', 'max:150'],
            'form.npwp' => ['nullable', 'string', 'max:50'],
            'form.address' => ['required', 'string', 'max:1000'],
            'form.phone' => ['nullable', 'string', 'max:50'],
            'form.email' => ['nullable', 'email', 'max:100'],
            'form.pic_name' => ['nullable', 'string', 'max:100'],
            'form.payment_term_days' => ['required', 'integer', 'min:0'],
            'form.credit_limit' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = $this->form;
        $data['payment_term_days'] = ($data['payment_term_days'] === null || $data['payment_term_days'] === '') ? 30 : (int) $data['payment_term_days'];
        $data['credit_limit'] = ($data['credit_limit'] === null || $data['credit_limit'] === '') ? 0 : (float) $data['credit_limit'];

        foreach (['npwp', 'phone', 'email', 'pic_name'] as $field) {
            if ($data[$field] === '') {
                $data[$field] = null;
            }
        }

        if ($this->editingId) {
            $supplier = Supplier::findOrFail($this->editingId);
            $supplier->update($data);
            $message = "Supplier {$supplier->name} diperbarui.";
            $event = 'update';
        } else {
            $supplier = Supplier::create($data);
            $message = "Supplier {$supplier->name} dibuat.";
            $event = 'create';
        }

        app(ActivityLogService::class)->log($event, $supplier, "{$event}d supplier: {$supplier->name}");

        session()->flash('status', $message);

        $this->redirectRoute('master.index', ['entity' => 'suppliers']);
    }

    public function cancel(): void
    {
        $this->redirectRoute('master.index', ['entity' => 'suppliers']);
    }

    public function render()
    {
        return view('livewire.master.supplier-form', [
            'editing' => $this->editingId !== null,
        ])->title('Supplier Form | Inventory System');
    }
}
