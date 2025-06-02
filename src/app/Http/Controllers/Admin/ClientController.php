<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {        
        $clients = Client::latest()->with('gameSessions')->paginate(10);
        return Inertia::render('Admin/Clients/Index', [
            'clients' => $clients,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Typically, for Inertia, you might not have a separate page
        // but the route and authorization still need to be in place.
        // If you have a dedicated create view: 
        // return Inertia::render('Admin/Clients/Create'); 
        // For now, let's assume the test just checks accessibility to the route.
        // If an actual view is needed by tests, it can be added.
        return Inertia::render('Admin/Clients/Create'); // Placeholder if a view is expected
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('[ClientController@store] Attempting to store client.', $request->all());
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:clients,email',
                'phone' => 'nullable|string|max:20',
                'password' => 'required|string|min:8|confirmed',
                'is_active' => 'required|boolean',
            ]);
            Log::info('[ClientController@store] Validation successful.', $validatedData);

            $client = Client::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone' => $validatedData['phone'] ?? null,
                'password' => Hash::make($validatedData['password']),
                'is_active' => $validatedData['is_active'],
            ]);
            Log::info('[ClientController@store] Client created successfully.', ['client_id' => $client->id]);

            return redirect()->route('admin.clients.index')->with('success', 'Client created successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('[ClientController@store] Validation Exception.', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            throw $e; // Re-throw validation exception
        } catch (\Exception $e) {
            Log::error('[ClientController@store] General Exception.', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);
            throw $e; // Re-throw to ensure test still sees 500, but check logs for details
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {       
        // Not typically used directly if edit view is preferred for admin panels
        return Inertia::render('Admin/Clients/Edit', ['client' => $client]); // Or a dedicated Show view
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        return Inertia::render('Admin/Clients/Edit', [
            'client' => $client,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:clients,email,' . $client->id,
            'phone' => 'nullable|string|max:20',
            'is_active' => 'required|boolean',
        ]);

        $client->update($validatedData);

        return redirect()->route('admin.clients.index')->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);
        $client->delete(); // Assuming SoftDeletes trait is used on Client model
        return redirect()->route('admin.clients.index')->with('success', 'Client deleted successfully.');
    }

    /**
     * Toggle the is_active status of the client.
     */
    public function toggleActive(Request $request, Client $client)
    {
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $client->update(['is_active' => $request->is_active]);
        
        return redirect()->route('admin.clients.index')->with('success', 'Client status updated.');
    }
} 