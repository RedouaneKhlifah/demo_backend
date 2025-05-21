<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Image;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductService
{
    protected $repository;

    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllProducts($searchTerm = null, $perPage = 10 ,$skipPagination = false)
    {
        $products = $this->repository->getAllWithSearch($searchTerm, $perPage , $skipPagination);

        return $products;
    }

    public function getAllArchivedProducts($searchTerm = null, $perPage = 10)
    {
        $products = $this->repository->getAllArchivedWithSearch($searchTerm, $perPage);

        return $products;
    }

    public function getProduct(Product $product)
    {
        $product = $this->repository->find($product) ->load('images');

        return $product;
    }

    public function createProduct(array $data, $imageFiles = [])
    {
        DB::beginTransaction();
    
        try {
            // Create the product
            $product = $this->repository->create($data);
    
            // Commit the transaction
            DB::commit();
    
            return $product->load('images');
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();
            throw $e; // Re-throw the exception for further handling
        }
    }

    public function updateProduct(Product $product, array $data, $imageFiles = [])
    {
        DB::beginTransaction();
    
        try {
            $this->deleteImages($product);    
            // Update the product
            $updatedProduct = $this->repository->update($product, $data);
    
            // Commit the transaction
            DB::commit();
    
            return $updatedProduct->load('images');;
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();
            throw $e; // Re-throw the exception for further handling
        }
    }

    public function deleteProduct(Product $product)
    {
        DB::beginTransaction();
    
        try {
            // Delete all associated images
            // $this->deleteImages($product);
    
            // Delete the product
            $deleted = $this->repository->delete($product);
    
            // Commit the transaction
            DB::commit();
    
            return $deleted;
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();
            throw $e; // Re-throw the exception for further handling
        }
    }

    /**
     * Delete multiple products by their IDs.
     */

    public function deleteProductsByIds(array $ids)
    {
        DB::beginTransaction();

        try {
            $deleted = $this->repository->bulkDelete($ids);
            DB::commit();

            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Store an uploaded image and return the path.
     */
    protected function storeImage($imageFile)
    {
        // Generate a unique filename
        $imageName = uniqid() . '_' . time() . '.' . $imageFile->getClientOriginalExtension();
        
        // Store the file in storage/app/public/product
        $imagePath = $imageFile->storeAs('public/product', $imageName);
        
        // Return the relative path (without the 'public/' prefix)
        return str_replace('public/', '', $imagePath);
    }

    protected function storeImages(Product $product, array $imageFiles)
    {
        foreach ($imageFiles as $index => $imageFile) {
            $imagePath = $this->storeImage($imageFile);

            // Create an image record for the product
            $product->images()->create([
                'url' => $imagePath,
                'position' => $index + 1, // Assign positions sequentially
            ]);
        }
    }
    /**
     * Delete an image file from storage.
     */
    protected function deleteImage(Image $image)
    {
        $relativePath = $image->getRawOriginal('url');
        if ($relativePath && Storage::exists('public/' . $relativePath)) {
            Storage::delete('public/' . $relativePath);
        }
    }

    protected function deleteImages(Product $product)
    {
        // Delete image files from storage
        foreach ($product->images as $image) {
            $this->deleteImage($image); // Pass the Image instance
        }
    
        // Delete image records from the database
        $product->images()->delete();
    }


    public function restoreProduct(int $id)
    {
        $product = $this->repository->restore($id);
    
        return $product;
    }

    
}