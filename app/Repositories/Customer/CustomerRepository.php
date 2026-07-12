<?php

namespace App\Repositories\Customer;

use App\Models\Customer;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function all()
    {
        return Customer::latest()->get();
    }

    public function paginate($limit = 20)
    {
        return Customer::latest()->paginate($limit);
    }

    public function find($id)
    {
        return Customer::findOrFail($id);
    }

    public function create(array $data)
    {
        return Customer::create($data);
    }

    public function update($id, array $data)
    {
        $customer = $this->find($id);
        $customer->update($data);
        return $customer;
    }

    public function delete($id)
    {
        $customer = $this->find($id);
        return $customer->delete();
    }
}
