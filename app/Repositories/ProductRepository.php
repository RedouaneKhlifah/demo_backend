<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductRepository
{
    protected $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function getAllWithSearch($searchTerm = null, $perPage = 10, $skipPagination = false)
    {
        $query = $this->model->newQuery();
    
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('sku', 'like', "%{$searchTerm}%");
            });
        }
    
        $query = $query->orderBy('created_at', 'desc');
        
        if ($skipPagination) {
            return $query->get();
        }
        
        return $query->paginate($perPage);
    }

    public function getAllArchivedWithSearch($searchTerm = null, $perPage = 10)
    {
        $query = $this->model->newQuery()->onlyTrashed();
    
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('sku', 'like', "%{$searchTerm}%");
            });
        }
    
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function find(Product $product)
    {
        return $product;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(Product $product, array $data)
    {
        $product->update($data);
        return $product;
    }

    public function delete(Product $product)
    {
        return $product->delete();
    }

    public function bulkDelete(array $ids)
    {
        return Product::whereIn('id', $ids)->delete();
    }

    public function restore(int $id)
    {
        $product = $this->model->withTrashed()->findOrFail($id);
    
        if (!$product->trashed()) {
            throw new ModelNotFoundException("product with ID {$id} is not deleted.");
        }
    
        $product->restore();
        return $product;
    }
}