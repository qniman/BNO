@extends('layouts.archive')

@section('title', 'Правка инцидента')

@section('content')
    @include('registry.incidents._form', [
        'incident' => $incident,
        'objects' => $objects,
        'action' => route('registry.incidents.update', $incident),
        'method' => 'PUT',
        'heading' => 'Правка инцидента №'.$incident->id,
    ])
@endsection
