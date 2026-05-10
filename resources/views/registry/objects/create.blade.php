@extends('layouts.archive')

@section('title', 'Новый объект')

@section('content')
    @include('registry.objects._form', [
        'action' => route('registry.objects.store'),
        'method' => 'POST',
        'object' => null,
        'heading' => 'Создание записи',
    ])
@endsection
