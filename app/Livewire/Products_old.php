<?php

// app/Livewire/Products.php

namespace App\Livewire;


use Livewire\Component;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.livewire-adminlte')]
class Products extends Component
{
    

    use WithPagination;
    
    public $code, $name, $productId;
    public $search = '';
    public $isEdit = false;
    protected $listeners = ['deleteProduct' => 'delete'];
    public $showForm = false;

    public $sortField;
    public $sortDirection;

    
    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->sortField = session('products_sortField', 'id');
        $this->sortDirection = session('products_sortDirection', 'desc');
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        session([
            'products_sortField' => $this->sortField,
            'products_sortDirection' => $this->sortDirection,
        ]);

        $this->resetPage();
    }

    
    public function getProductsProperty()
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

        return $query->orderBy($this->sortField, $this->sortDirection)->paginate(10);
    }

    public function render()
    {
        
        return view('livewire.products');
    }
    
    public function clearSearch()
    {
        $this->search = '';
        $this->resetPage();
        $this->refreshData();
    }
    
    public function applySearch()
    {
        $this->resetPage();
        $this->refreshData();
    }
    
    public function refreshData()
    {
        $this->dispatch('$refresh');
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

    protected function odooSyncMethod(): string
    {
        return 'syncProducts';
    }

    public function paginationView()
    {
        return 'components.pagination.spanish';
    }
}

