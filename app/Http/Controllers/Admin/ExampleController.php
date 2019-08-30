<?php

namespace App\Http\Controllers\Dashboard;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Transformers\ExampleTransformer;
use App\Http\Controllers\Traits\ResponseTrait;
use App\Repositories\Contracts\ExampleRepository;

class ActivityController extends Controller
{
    use ResponseTrait{
        ResponseTrait::__construct as responseTraitConstruct;
    }

    private $exampleRepository;
    protected $user;

    public function __construct(ExampleRepository $exampleRepository)
    {
        $this->responseTraitConstruct();
        $this->exampleRepository = $exampleRepository;
        $this->user= Auth::user();
    }

    public function index(Request $request)
    {
        $results= $this->exampleRepository->getList($request->all());
        return $this->respondWithPagination($results, new ExampleTransformer);
    }

    public function show($id)
    {
        $result= $this->exampleRepository->findOne($id);
        return $this->respondWithItem($result, new ExampleTransformer);
    }

}
