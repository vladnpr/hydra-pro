@extends('adminlte::page')

@section('title', 'Польоти Vampire')

@section('content_header')
    <h1>Польоти Vampire</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-body text-center">
            <h4>У вас немає активного чергування Vampire</h4>
            <p>Для роботи з польотами спочатку розпочніть або приєднайтеся до чергування.</p>
            <a href="{{ route('vampire.combat_shifts.index') }}" class="btn btn-primary">
                Перейти до чергувань
            </a>
        </div>
    </div>
@endsection
