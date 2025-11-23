@extends('layouts.app')

@section('title', 'Új Város')

@section('content')
<h1>Új Város Hozzáadása</h1>

@if($errors->any())
<div class="error">
    <ul>
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('cities.store') }}" method="POST">
    @csrf
    
    <label for="name">Város neve: *</label>
    <input type="text" name="name" id="name" required value="{{ old('name') }}">
    
    <label for="county_id">Megye: *</label>
    <select name="county_id" id="county_id" required>
        <option value="">-- Válassz megyét --</option>
        @foreach($counties as $county)
            <option value="{{ $county->id }}" {{ old('county_id') == $county->id ? 'selected' : '' }}>
                {{ $county->name }}
            </option>
        @endforeach
    </select>
    
    <label for="postal_codes">Irányítószámok (vesszővel elválasztva): *</label>
    <input type="text" name="postal_codes" id="postal_codes" required value="{{ old('postal_codes') }}" placeholder="pl: 1011, 1012, 1013">
    <small>Több irányítószám esetén vesszővel válaszd el őket</small>
    
    <p>
        <button type="submit">Mentés</button>
        <a href="{{ route('cities.index') }}" class="btn">Mégse</a>
    </p>
</form>
@endsection