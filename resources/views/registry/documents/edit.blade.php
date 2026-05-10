@extends('layouts.archive')

@section('title', 'Правка документа')

@section('content')
    @include('registry.documents._form', [
        'document' => $document,
        'action' => route('registry.documents.update', $document),
        'method' => 'PUT',
        'heading' => 'Правка документа',
    ])
@endsection
