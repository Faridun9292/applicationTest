<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ApplicationStoreRequest;
use App\Http\Requests\Api\ApplicationUpdateRequest;
use App\Jobs\SendEmailJob;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Queue\Jobs\Job;

class ApplicationController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/requests",
     *      operationId="getRequests",
     *      tags={"Requests"},
     *      summary="Gets all requests",
     *      description="Returns collection of all Applications pagninated 30 records in every page, also can be filtered by email, status
     *      from-to (date) name ",
     *      @OA\Response(
     *          response=200,
     *          description="success",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */

    public function index(Request $request)
    {
        return Application::query()
            ->when($request->query('status'), fn($query) => $query->where('status', $request->query('status')))
            ->when($request->query('from'), fn($query) => $query->whereDate('created_at', '>=', $request->query('from')))
            ->when($request->query('to'), fn($query) => $query->whereDate('created_at', '<=', $request->query('to')))
            ->when($request->query('email'), fn($query) => $query->where('email', 'like', '%'.$request->query('email').'%'))
            ->when($request->query('name'), fn($query) => $query->where('name', 'like', '%'.$request->query('name').'%'))
            ->latest()
            ->paginate(30)
            ->withQueryString();
    }

    /**
     * @OA\Post(
     *     path="/api/requests",
     *     operationId="storeRequest",
     *     tags={"Requests"},
     *     summary="Adds a new request",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="name",
     *                     type="string, required, max:255",
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="email, required",
     *                 ),
     *                  @OA\Property(
     *                     property="message",
     *                     type="string, required",
     *                 ),
     *                 example={
     *                      "name": "Иван",
     *                      "email": "invan@mail.ru",
     *                      "message": "Тестовый текст",
     *                  }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *     ),
     *     @OA\Response(
     *          response=422,
     *          description="Validation Error"
     *      ),
     * )
     */

    public function store(ApplicationStoreRequest $request)
    {
        return  Application::create($request->validated());
    }

    /**
     * @OA\Put(
     *      path="/api/requests/{id}",
     *      operationId="updateRequest",
     *      tags={"Requests"},
     *      summary="Updates the request",
     *      description="Returns updated request and if status Resolved sends email to the user",
     *      @OA\Parameter(
     *          name="id",
     *          description="Requests id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="status",
     *                     type="integer, in:1,2",
     *                 ),
     *                 @OA\Property(
     *                     property="comment",
     *                     type="nullable, string, required_if:status,2",
     *                 ),
     *                 example={
     *                      "status": "2",
     *                      "comment": "Ответ на ваш запрос",
     *                  }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="success",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation Error"
     *      ),
     * )
     */

    public function update(ApplicationUpdateRequest $request, $id)
    {
        $application =  Application::findOrFail($id);

        $application->update($request->validated());

        SendEmailJob::dispatch($application);

        return $application;
    }

    /**
     * @OA\Delete(
     *      path="/api/requests/{id}",
     *      operationId="deletesRequest",
     *      tags={"Requests"},
     *      summary="Deletes request",
     *      description="Deletes the request by id",
     *      @OA\Parameter(
     *          name="id",
     *          description="Requests id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="success",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      ),
     * )
     */

    public function destroy($id)
    {
        Application::findOrFail($id)->delete();

        return response()->noContent();
    }
}
