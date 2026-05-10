@extends('layouts.archive')

@section('title', 'Новый документ')

@section('content')
    @include('registry.documents._form', [
        'document' => null,
        'action' => route('registry.documents.store'),
        'method' => 'POST',
        'heading' => 'Новый документ',
    ])
@endsection
