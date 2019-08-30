<?php //app/Http/Controllers/ResponseTrait.php

namespace App\Http\Controllers\Traits;

use App\Transformer\Serializer\CustomDataArraySerializer;
use Illuminate\Database\Eloquent\Model;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

trait ResponseTrait
{
    /**
     * Status code of response
     *
     * @var int
     */
    protected $statusCode = 200;

    /**
     * Fractal manager instance
     *
     * @var Manager
     */
    protected $fractal;


    public function __construct()
    {
        $this->setFractal();
    }

    /**
     * Set fractal Manager instance
     *
     * @param Manager $fractal
     * @return void
     */
    public function setFractal()
    {
        $this->fractal = new Manager();

        $this->fractal->setSerializer(new CustomDataArraySerializer());
    }

    /**
     * Getter for statusCode
     *
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Setter for statusCode
     *
     * @param int $statusCode Value to set
     *
     * @return self
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Send custom data response
     *
     * @param $status
     * @param $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendCustomResponse($status, $message)
    {
        return response()->json(['status' => $status, 'message' => $message], $status);
    }

    /**
     * Send empty data response
     *
     * @return string
     */
    public function sendEmptyDataResponse()
    {
        return response()->json(['data' => new \StdClass()]);
    }

    /**
     * Return collection response from the application
     *
     * @param array|LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection $collection
     * @param \Closure|TransformerAbstract $callback
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithCollection($collection, $callback)
    {
        // $this->fractal= new Manager();
        $resource = new Collection($collection, $callback);

        //set empty data pagination
        if (empty($collection)) {
            $collection = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
            $resource = new Collection($collection, $callback);
        }
        $resource->setPaginator(new IlluminatePaginatorAdapter($collection));

        $rootScope = $this->fractal->createData($resource);
        $data=['status'=>200,'message'=>'OK'] + $rootScope->toArray();
        return $this->respondWithArray($data);
    }

    protected function respondWithPagination($collection, $callback, $included =null)
    {
        $resource = new Collection($collection, $callback);
        $trans = $this->fractal;
        if($included != null){
            $trans->parseIncludes($included);
        }
        $rootScope = $trans->createData($resource);
        $total_pages = ceil($collection->total() / $collection->perPage());
        $data = array_merge(['status'=>200,'message'=>'OK'], $rootScope->toArray(), [
            'paginator' => [
                'total_count' => $collection->total(),
                'total_pages' => $total_pages,
                'first_page' => $collection->url(1),
                'prev_page'=> $collection->previousPageUrl(),
                'next_page' => $collection->nextPageUrl(),
                'last_page' => $collection->url($total_pages),
                'limit' => $collection->perPage()
            ]
        ]);
        return $this->respond($data);
    }

    /**
     * Return single item response from the application
     *
     * @param Model $item
     * @param \Closure|TransformerAbstract $callback
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithItem($item, $callback, $included=null)
    {
        // $this->fractal= new Manager();
        $resource = new Item($item, $callback);
        $trans = $this->fractal;
        if($included != null){
            $trans->parseIncludes($included);
        }
        $rootScope = $trans->createData($resource);
        $data=['status'=>200,'message'=>'OK'] + $rootScope->toArray();
        return $this->respondWithArray($data);
    }

    /**
     * Return a json response from the application
     *
     * @param array $array
     * @param array $headers
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithArray(array $array, array $headers = [])
    {
        return response()->json($array, $this->statusCode, $headers);
    }

    protected function respondWithCollectionNoPaging($collection, $callback)
    {
        // $this->fractal= new Manager();
        $resource = new Collection($collection, $callback);

        $rootScope = $this->fractal->createData($resource);
        $data=['status'=>200,'message'=>'OK'] + $rootScope->toArray();
        return $this->respondWithArray($data);
    }

    /**
     * 404 Not Found
     * @param  string $message [description]
     * @return [type]          [description]
     */
    public function respondNotFound($message = 'Not Found!')
    {
        return $this->setStatusCode(404)->respondWithError($message);
    }

    /**
     * 401 Unauthorized
     * @param  string $message [description]
     * @return [type]          [description]
     */
    public function respondUnauthorized($message='Unauthorized!')
    {
        return $this->setStatusCode(401)->respondWithError($message);
    }

    /**
     * 400 Bad Request
     * @param  string $message [description]
     * @return [type]          [description]
     */
    public function sendInvalidFieldResponse($errors)
    {
        return response()->json((['status' => 400, 'invalid_fields' => $errors]), 400);
    }

    /**
     * 400 Bad Request
     * @param  string $message [description]
     * @param  [type] $errors  [description]
     * @return [type]          [description]
     */
    public function respondBadRequest($message = 'The request was invalid or cannot be served!', $errors)
    {
        return $this->setStatusCode(400)->respondWithErrorField($message, $errors);
    }

    /**
     * 403 Forbidden Response
     * @param  string $message [description]
     * @return [type]          [description]
     */
    public function respondForbidden($message = 'Insufficient privileges to perform this action!')
    {
        return $this->setStatusCode(403)->respondWithError($message);
    }

    /**
     * 503 Service Unavailable
     * @param  string $message [description]
     * @return [type]          [description]
     */
    public function respondServiceUnavailable($message = "Service Unavailable!")
    {
        return $this->setStatusCode(503)->respondWithError($message);
    }

    /**
     * 500 Internal Server Error
     * @param  string $message [description]
     * @return [type]          [description]
     */
    public function respondInternalError($message = 'Internal Error!')
    {
        return $this->setStatusCode(500)->respondWithError($message);
    }

    /**
     * 201 Created
     * @param  string $message [description]
     * @return [type]          [description]
     */
    protected function respondCreated($message='New resource has been created')
    {
        return $this->setStatusCode(201)
            ->respond([
                'status' => $this->getStatusCode(),
                'message' => $message
            ]);
    }

    /**
     * 201 Created
     * @param  string $message [description]
     * @return [type]          [description]
     */
    protected function respondSuccess($message='Ok')
    {
        return $this->setStatusCode(200)
            ->respond([
                'status' => $this->getStatusCode(),
                'message' => $message
            ]);
    }

    /**
     * 204 Deleted
     * @param  string $message [description]
     * @return [type]          [description]
     */
    protected function respondDeleted($message='The resource was successfully deleted')
    {
        return $this->setStatusCode(204)
            ->respond([
                'message' => $message
            ]);
    }

    /**
     * 422 Unprocessable Entity
     * @param  string $message [description]
     * @return [type]          [description]
     */
    protected function respondUnprocessableEntity($message='Unprocessable Entity')
    {
        return $this->setStatusCode(422)
            ->respond([
                'status' => $this->getStatusCode(),
                'message' => $message
            ]);
    }

    /**
     * 200 Ok as Default
     * @param  [type] $data    [description]
     * @param  array  $headers [description]
     * @return [type]          [description]
     */
    public function respond($data, $headers=[])
    {
        return response()->json($data, $this->getStatusCode(), $headers);
    }

    /**
     * Show Custom Error Message
     * @param  [type] $message [description]
     * @return [type]          [description]
     */
    public function respondWithError($message)
    {
        return $this->respond([
                'status' => $this->getStatusCode(),
                'message' => $message,
        ]);
    }

    public function respondWithErrorField($message, $errors)
    {
        return $this->respond([
                'status' => $this->getStatusCode(),
                'message' => $message,
                'errors' => $errors
        ]);
    }
}
