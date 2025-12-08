@extends('layouts.app')

@section('title', 'Megye Megtekintése')

@section('content')
<h1>{{ $county->name }} Megye</h1>

<p><a href="{{ route('counties.index') }}" class="btn">Vissza a listához</a></p>

<h2>Alapadatok</h2>
<table>
    <tr>
        <th>Megye neve:</th>
        <td>{{ $county->name }}</td>
    </tr>
    <tr>
        <th>Városok száma:</th>
        <td>{{ $county->cities->count() }}</td>
    </tr>
    <tr>
        <th>Létrehozva:</th>
        <td>{{ $county->created_at->format('Y-m-d H:i') }}</td>
    </tr>
</table>

<h2>Városok</h2>
@if($county->cities->isNotEmpty())
<table>
    <thead>
        <tr>
            <th>Város neve</th>
            <th>Irányítószámok</th>
        </tr>
    </thead>
    <tbody>
        @foreach($county->cities as $city)
        <tr>
            <td>{{ $city->name }}</td>
            <td>
                @foreach($city->postalCodes as $pc)
                    {{ $pc->code }}{{ !$loop->last ? ', ' : '' }}
                @endforeach
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<p>Még nincs város ebben a megyében.</p>
@endif
@endsection