@extends('layouts.app')

@section('title', 'Megye Szerkesztése')

@section('content')
<h1>Megye Szerkesztése</h1>

@if($errors->any())
<div class="error">
    <ul>
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('counties.update', $county) }}" method="POST">
    @csrf
    @method('PUT')
    
    <label for="name">Megye neve:</label>
    <input type="text" name="name" id="name" required value="{{ old('name', $county->name) }}">
    
    <p>
        <button type="submit">Mentés</button>
        <a href="{{ route('counties.index') }}" class="btn">Mégse</a>
    </p>
</form>
@endsection