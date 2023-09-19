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

    public function store(ApplicationStoreRequest $request)
    {
        return  Application::create($request->validated());
    }

    public function update(ApplicationUpdateRequest $request, $id)
    {
        $application =  Application::findOrFail($id);

        $application->update($request->validated());

        SendEmailJob::dispatch($application);

        return $application;
    }

    public function destroy($id)
    {
        Application::findOrFail($id)->delete();

        return response()->noContent();
    }
}
