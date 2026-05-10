@extends('layouts.archive')

@section('title', 'Новый инцидент')

@section('content')
    @include('registry.incidents._form', [
        'incident' => null,
        'objects' => $objects,
        'action' => route('registry.incidents.store'),
        'method' => 'POST',
        'heading' => 'Регистрация инцидента',
    ])
@endsection
