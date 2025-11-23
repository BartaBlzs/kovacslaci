@extends('layouts.app')

@section('title', 'Új Megye')

@section('content')
<h1>Új Megye Hozzáadása</h1>

@if($errors->any())
<div class="error">
    <ul>
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('counties.store') }}" method="POST">
    @csrf
    
    <label for="name">Megye neve:</label>
    <input type="text" name="name" id="name" required value="{{ old('name') }}">
    
    <p>
        <button type="submit">Mentés</button>
        <a href="{{ route('counties.index') }}" class="btn">Mégse</a>
    </p>
</form>
@endsection