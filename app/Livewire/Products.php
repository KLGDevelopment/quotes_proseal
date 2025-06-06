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
    public $isEdit = false;



    public function render()
    {
        $this->products = Product::orderBy('id', 'desc')->get();
        return view('livewire.products');
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
    }

    public function delete($id)
    {
        Product::destroy($id);
    }

    public function resetForm()
    {
        $this->reset(['code', 'name', 'productId', 'isEdit']);
    }
}

