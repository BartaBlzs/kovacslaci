@extends('layouts.app')

@section('title', 'Főoldal')

@section('content')
<h1>Üdvözöl a Magyar Városok Alkalmazás</h1>

<p>Bejelentkezve mint: <strong>{{ Auth::user()->name }}</strong></p>

<h2>Statisztikák</h2>
<ul>
    <li>Megyék száma: <strong>{{ \App\Models\County::count() }}</strong></li>
    <li>Városok száma: <strong>{{ \App\Models\City::count() }}</strong></li>
    <li>Irányítószámok száma: <strong>{{ \App\Models\PostalCode::count() }}</strong></li>
</ul>

<h2>Gyors hivatkozások</h2>
<ul>
    <li><a href="{{ route('counties.index') }}">Megyék kezelése</a></li>
    <li><a href="{{ route('cities.index') }}">Városok kezelése</a></li>
</ul>
@endsection