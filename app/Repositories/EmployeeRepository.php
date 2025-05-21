<?php

namespace App\Repositories;

use App\Models\Employee;
use App\Models\HistoryOfPay;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class EmployeeRepository
{
    protected $model;

    public function __construct(Employee $model)
    {
        $this->model = $model;
    }

    public function getAllWithSearch($searchTerm = null, $perPage = 10)
    {
        $query = $this->model->newQuery();

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('last_name', 'like', "%{$searchTerm}%")
                  ->orWhere('national_id', 'like', "%{$searchTerm}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getAllArchivedWithSearch($searchTerm = null, $perPage = 10)
    {
        $query = $this->model->newQuery()->onlyTrashed();

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('last_name', 'like', "%{$searchTerm}%")
                  ->orWhere('national_id', 'like', "%{$searchTerm}%");
            });
        }
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function find(Employee $employee)
    {
        return $employee;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(Employee $employee, array $data)
    {
        $employee->update($data);
        return $employee;
    }

    public function delete(Employee $employee)
    {
        return $employee->delete();
    }

    public function bulkDelete(array $ids)
    {
        return Employee::whereIn('id', $ids)->delete();
    }

    public function storeHistory(array $data)
    {
        return HistoryOfPay::create($data);
    }
    public function restore(int $id)
    {
        $employee = $this->model->withTrashed()->findOrFail($id);
    
        if (!$employee->trashed()) {
            throw new ModelNotFoundException("employee with ID {$id} is not deleted.");
        }
    
        $employee->restore();
        return $employee;
    }
}
