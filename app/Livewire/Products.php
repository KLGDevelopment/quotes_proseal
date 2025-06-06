<?php

// app/Livewire/Products.php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use Livewire\Attributes\Layout;

#[Layout('layouts.livewire-adminlte')]
class Products extends Component
{
    public $products, $code, $name, $productId;
    public $search = '';
    public $isEdit = false;
    protected $listeners = ['deleteProduct' => 'delete'];
    public $showForm = false;



    public function render()
    {
        $query = Product::query();

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $this->products = $query->orderBy('id', 'desc')->get();

        return view('livewire.products');
    }

    public function create()
    {
        $this->reset(['code', 'name', 'productId', 'isEdit']);
        $this->showForm = true;
    }

public function save()
{
    $this->validate([
        'code' => 'required|unique:products,code,' . $this->productId,
        'name' => 'required|string|min:2',
    ]);

    if ($this->productId) {
        Product::find($this->productId)?->update([
            'code' => $this->code,
            'name' => $this->name,
        ]);
    } else {
        Product::create([
            'code' => $this->code,
            'name' => $this->name,
        ]);
    }

    $this->resetForm();
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $this->productId = $product->id;
        $this->code = $product->code;
        $this->name = $product->name;
        $this->isEdit = true;
        $this->showForm = true;
    }

    public function delete($id)
    {
        Product::destroy($id);
    }

    public function resetForm()
    {
        $this->reset(['code', 'name', 'productId', 'isEdit']);
        $this->showForm = false;
    }
}

