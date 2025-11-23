<?php

use Illuminate\Support\Facades\Route;
use App\Models\County;
use App\Models\City;
use App\Models\PostalCode;
use Barryvdh\DomPDF\Facade\Pdf;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Auth - Login
Route::get('/login', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('auth.login');
})->name('login');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (auth()->attempt($credentials, $request->filled('remember'))) {
        $request->session()->regenerate();
        
        $token = auth()->user()->createToken('web-token')->plainTextToken;
        session(['api_token' => $token]);
        
        return redirect()->intended(route('dashboard'))->with('success', 'Sikeres bejelentkezés!');
    }

    return back()->withErrors([
        'email' => 'A megadott adatok nem egyeznek.',
    ])->onlyInput('email');
})->name('login.post');

// Auth - Register
Route::get('/register', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('auth.register');
})->name('register');

Route::post('/register', function (\Illuminate\Http\Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $user = \App\Models\User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
    ]);

    auth()->login($user);
    
    $token = $user->createToken('web-token')->plainTextToken;
    session(['api_token' => $token]);

    return redirect()->route('dashboard')->with('success', 'Sikeres regisztráció!');
})->name('register.post');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');
    
    // Logout
    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        // Sanctum tokenek törlése
        if ($request->user()) {
            $request->user()->tokens()->delete();
        }
        
        // Web guard kijelentkezés
        \Illuminate\Support\Facades\Auth::guard('web')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Sikeres kijelentkezés!');
    })->name('logout');
    
    // ============================================
    // COUNTIES
    // ============================================
    
    // List
    Route::get('/counties', function () {
        $counties = County::with('cities')->orderBy('name')->get();
        return view('counties.index', compact('counties'));
    })->name('counties.index');
    
    // Create Form
    Route::get('/counties/create', function () {
        return view('counties.create');
    })->name('counties.create');
    
    // Store
    Route::post('/counties', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:counties',
        ]);

        County::create($validated);

        return redirect()->route('counties.index')->with('success', 'Megye sikeresen létrehozva!');
    })->name('counties.store');
    
    // Show
    Route::get('/counties/{county}', function (County $county) {
        $county->load('cities.postalCodes');
        return view('counties.show', compact('county'));
    })->name('counties.show');
    
    // Edit Form
    Route::get('/counties/{county}/edit', function (County $county) {
        return view('counties.edit', compact('county'));
    })->name('counties.edit');
    
    // Update
    Route::put('/counties/{county}', function (\Illuminate\Http\Request $request, County $county) {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:counties,name,' . $county->id,
        ]);

        $county->update($validated);

        return redirect()->route('counties.index')->with('success', 'Megye sikeresen frissítve!');
    })->name('counties.update');
    
    // Delete
    Route::delete('/counties/{county}', function (County $county) {
        $county->delete();
        return redirect()->route('counties.index')->with('success', 'Megye sikeresen törölve!');
    })->name('counties.destroy');
    
    // CSV Export
    Route::get('/counties-export-csv', function () {
        $counties = County::with('cities')->orderBy('name')->get();

        $filename = 'megyek_' . date('Y-m-d_His') . '.csv';
        $handle = fopen('php://output', 'w');
        
        ob_start();
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($handle, ['Megye név', 'Városok száma'], ';');
        
        foreach ($counties as $county) {
            fputcsv($handle, [
                $county->name,
                $county->cities->count()
            ], ';');
        }
        
        fclose($handle);
        $csv = ob_get_clean();

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    })->name('counties.export.csv');
    
    // PDF Export
    Route::get('/counties-export-pdf', function () {
        $counties = County::with('cities')->orderBy('name')->get();
        $pdf = Pdf::loadView('counties.pdf', compact('counties'));
        return $pdf->download('megyek_' . date('Y-m-d_His') . '.pdf');
    })->name('counties.export.pdf');
    
    // ============================================
    // CITIES
    // ============================================
    
    // List with ABC filter
    Route::get('/cities', function (\Illuminate\Http\Request $request) {
        $counties = County::orderBy('name')->get();
        $selectedCounty = $request->county_id;
        $selectedLetter = $request->letter;
        
        $cities = collect();
        $letters = collect();
        
        if ($selectedCounty) {
            $letters = City::where('county_id', $selectedCounty)
                ->selectRaw('DISTINCT UPPER(SUBSTRING(name, 1, 1)) as letter')
                ->orderBy('letter')
                ->pluck('letter');
            
            if ($selectedLetter) {
                $cities = City::with(['county', 'postalCodes'])
                    ->where('county_id', $selectedCounty)
                    ->where('name', 'like', $selectedLetter . '%')
                    ->orderBy('name')
                    ->get();
            }
        }
        
        return view('cities.index', compact('counties', 'cities', 'letters', 'selectedCounty', 'selectedLetter'));
    })->name('cities.index');
    
    // Create Form
    Route::get('/cities/create', function () {
        $counties = County::orderBy('name')->get();
        return view('cities.create', compact('counties'));
    })->name('cities.create');
    
    // Store
    Route::post('/cities', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'county_id' => 'required|exists:counties,id',
            'postal_codes' => 'required|string',
        ]);

        $city = City::create([
            'name' => $validated['name'],
            'county_id' => $validated['county_id']
        ]);

        $codes = array_map('trim', explode(',', $validated['postal_codes']));
        foreach ($codes as $code) {
            if (strlen($code) === 4 && is_numeric($code)) {
                $city->postalCodes()->create(['code' => $code]);
            }
        }

        return redirect()->route('cities.index')->with('success', 'Város sikeresen létrehozva!');
    })->name('cities.store');
    
    // Show
    Route::get('/cities/{city}', function (City $city) {
        $city->load('county', 'postalCodes');
        return view('cities.show', compact('city'));
    })->name('cities.show');
    
    // Edit Form
    Route::get('/cities/{city}/edit', function (City $city) {
        $counties = County::orderBy('name')->get();
        $city->load('postalCodes');
        return view('cities.edit', compact('city', 'counties'));
    })->name('cities.edit');
    
    // Update
    Route::put('/cities/{city}', function (\Illuminate\Http\Request $request, City $city) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'county_id' => 'required|exists:counties,id',
            'postal_codes' => 'required|string',
        ]);

        $city->update([
            'name' => $validated['name'],
            'county_id' => $validated['county_id']
        ]);

        $city->postalCodes()->delete();
        $codes = array_map('trim', explode(',', $validated['postal_codes']));
        foreach ($codes as $code) {
            if (strlen($code) === 4 && is_numeric($code)) {
                $city->postalCodes()->create(['code' => $code]);
            }
        }

        return redirect()->route('cities.index')->with('success', 'Város sikeresen frissítve!');
    })->name('cities.update');
    
    // Delete
    Route::delete('/cities/{city}', function (City $city) {
        $city->delete();
        return redirect()->route('cities.index')->with('success', 'Város sikeresen törölve!');
    })->name('cities.destroy');
    
    // CSV Export
    Route::get('/cities-export-csv', function (\Illuminate\Http\Request $request) {
        $query = City::with(['county', 'postalCodes'])->orderBy('name');
        
        if ($request->county_id) {
            $query->where('county_id', $request->county_id);
        }
        if ($request->letter) {
            $query->where('name', 'like', $request->letter . '%');
        }
        
        $cities = $query->get();

        $filename = 'varosok_' . date('Y-m-d_His') . '.csv';
        $handle = fopen('php://output', 'w');
        
        ob_start();
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($handle, ['Város név', 'Megye', 'Irányítószámok'], ';');
        
        foreach ($cities as $city) {
            fputcsv($handle, [
                $city->name,
                $city->county->name,
                $city->postalCodes->pluck('code')->implode(', ')
            ], ';');
        }
        
        fclose($handle);
        $csv = ob_get_clean();

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    })->name('cities.export.csv');
    
    // PDF Export
    Route::get('/cities-export-pdf', function (\Illuminate\Http\Request $request) {
        $query = City::with(['county', 'postalCodes'])->orderBy('name');
        
        if ($request->county_id) {
            $query->where('county_id', $request->county_id);
        }
        if ($request->letter) {
            $query->where('name', 'like', $request->letter . '%');
        }
        
        $cities = $query->get();
        
        $pdf = Pdf::loadView('cities.pdf', compact('cities'));
        
        return $pdf->download('varosok_' . date('Y-m-d_His') . '.pdf');
    })->name('cities.export.pdf');
});