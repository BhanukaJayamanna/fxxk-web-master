<?php

namespace Modules\MultiVendor\Services;

use Illuminate\Support\Facades\Validator;
use \Modules\MultiVendor\Repositories\CommisionRepository;
use App\Traits\ImageStore;

class CommisionService
{
    use ImageStore;

    protected $commisionRepository;

    public function __construct(CommisionRepository $commisionRepository)
    {
        $this->commisionRepository = $commisionRepository;
    }

    public function getAll()
    {
        return $this->commisionRepository->getAll();
    }

    public function findByID($id)
    {
        return $this->commisionRepository->findByID($id);
    }


    public function update($data, $id)
    {
        return $this->commisionRepository->update($data, $id);
    }
}
