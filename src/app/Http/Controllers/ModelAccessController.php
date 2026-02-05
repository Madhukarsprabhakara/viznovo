<?php

namespace App\Http\Controllers;

use App\Models\ModelAccess;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Crypt;
class ModelAccessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $modelAccesses = ModelAccess::where('user_id', auth()->id())->get();

        return Inertia::render('settings/ApiKeys', [
            'modelAccesses' => $modelAccesses,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $data = $request->validate([
            // send one or both from the form
           'model_key' => ['required', 'string', 'max:255'],
            'token' => ['required', 'string', 'max:5000'],
        ]);

        $userId = auth()->id();

        ModelAccess::updateOrCreate(
            [
                'user_id' => $userId,
                'model_key' => $data['model_key'],
            ],
            [
                'token' => Crypt::encryptString($data['token']),
            ]
        );
    
                   

        return redirect()->back()->with('success', 'API keys updated.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ModelAccess $modelAccess)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ModelAccess $modelAccess)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ModelAccess $modelAccess)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ModelAccess $modelAccess)
    {
        //
    }
}
