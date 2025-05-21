<?php

namespace App\Services;

use App\Models\Employee;
use App\Repositories\EmployeeRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeService
{
    protected $repository;

    public function __construct(EmployeeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllEmployees($searchTerm = null, $perPage = 10)
    {
        return $this->repository->getAllWithSearch($searchTerm, $perPage);
    }

    public function getAllArchivedEmployees($searchTerm = null, $perPage = 10)
    {
        return $this->repository->getAllArchivedWithSearch($searchTerm, $perPage);
    }

    public function getEmployee(Employee $employee)
    {
        return $this->repository->find($employee);
    }

    public function createEmployee(array $data)
    {
        return $this->repository->create($data);
    }

    public function updateEmployee(Employee $employee, array $data)
    {
        return $this->repository->update($employee, $data);
    }

    public function deleteEmployee(Employee $employee)
    {
        return $this->repository->delete($employee);
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

    public function storeEmployeePayHistory(array $data, Employee $employee)
    {
        // Format data before saving
        $formattedData = [
            'employee_id' => $employee->id,
            'total_ton' => $data['total_ton'],
            'price_per_ton' => $data['price_per_ton'], // Assuming price is stored in Employee
            'total_gain' => $data['total_ton'] * $data['price_per_ton'], // Calculated value
            'start_date' => date('Y-m-d H:i:s', strtotime($data['start_date'])), // Fix Date Format
            'end_date' => date('Y-m-d H:i:s', strtotime($data['end_date'])),     // Fix Date Format
        ];
    
        return $this->repository->storeHistory($formattedData);
    }

    public function restoreEmployee(int $id)
    {
        return $this->repository->restore($id);
    }
    
    
}
