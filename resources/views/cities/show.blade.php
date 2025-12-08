@extends('layouts.app')

@section('title', 'Város Megtekintése')

@section('content')
<h1>{{ $city->name }}</h1>

<p><a href="{{ route('cities.index') }}" class="btn">Vissza a listához</a></p>

<h2>Alapadatok</h2>
<table>
    <tr>
        <th>Város neve:</th>
        <td>{{ $city->name }}</td>
    </tr>
    <tr>
        <th>Megye:</th>
        <td>{{ $city->county->name }}</td>
    </tr>
    <tr>
        <th>Irányítószámok:</th>
        <td>
            @foreach($city->postalCodes as $pc)
                {{ $pc->code }}{{ !$loop->last ? ', ' : '' }}
            @endforeach
        </td>
    </tr>
    <tr>
        <th>Létrehozva:</th>
        <td>{{ $city->created_at->format('Y-m-d H:i') }}</td>
    </tr>
</table>
@endsection