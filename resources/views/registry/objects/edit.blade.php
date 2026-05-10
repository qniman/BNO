@extends('layouts.archive')

@section('title', 'Правка '.$object->number)

@section('content')
    @if (session('status'))
        <div class="mb-4 border border-bno-border bg-bno-panel px-4 py-3 text-xs uppercase text-bno-active">
            {{ session('status') }}
        </div>
    @endif

    @include('registry.objects._form', [
        'action' => route('registry.objects.update', $object),
        'method' => 'PUT',
        'object' => $object,
        'heading' => 'Правка записи',
    ])

    @include('registry.objects._attachments_manage')

    @include('registry.objects._appendices_manage')
@endsection
