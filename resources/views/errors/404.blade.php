@extends('layouts.app')

@section('title', 'Puslapis nerastas')
@section('page', 'error')

@section('content')
    <div class="container error-page fade-in">
        <img src="{{ asset('images/empty.svg') }}" alt="" width="200" height="160">
        <span class="error-code">404</span>
        <h1>Puslapis nerastas</h1>
        <p class="muted">Tokio puslapio nėra arba jis buvo perkeltas.</p>
        <a class="btn btn-primary" href="{{ route('home') }}"><i data-lucide="house"></i> Į pradžią</a>
    </div>
@endsection
