<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules;
use App\Models\User;

class UserController extends Controller
{

public function getOptions(Request $request)
{
    $query = $request->get('q');

    // Replace this with your own data fetching logic
    /*$data = [
        ['id' => 1, 'text' => 'Option 1'],
        ['id' => 2, 'text' => 'Option 2'],
        ['id' => 3, 'text' => 'Option 3'],
    ];*/

    $data = User::query()->get(['id', 'name as text'])->toArray();



    // Filter the data based on the query if needed
    if ($query) {
        $data = array_filter($data, function ($item) use ($query) {
            return stripos($item['text'], $query) !== false;
        });
    }

    return response()->json(array_values($data));
}

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $users = User::query()->get()->toArray();
        return view('app.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('tenants.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:tenants|max:255',
            'domain_name' => 'required|string|unique:domains,domain|max:255',
            'password' => ['required','string','confirmed', Rules\Password::defaults()],
//            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $input = $request->all();
        $tenant = Tenant::query()->create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        $tenant->domains()->create([
            'domain' => $input['domain_name'] . '.' . config('app.domain')
        ]);
        return redirect()->route('tenants.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tenant $tenant)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tenant $tenant)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tenant $tenant)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tenant $tenant)
    {
        //
    }
}
