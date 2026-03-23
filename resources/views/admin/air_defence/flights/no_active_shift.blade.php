@extends('adminlte::page')

@section('title', 'Вильоти ППО')

@section('content_header')
    <h1>Вильоти ППО</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-body text-center">
            <h4>У вас немає активного чергування ППО</h4>
            <p>Для роботи з вильотами спочатку розпочніть або приєднайтеся до чергування.</p>
            <a href="{{ route('air-defence.combat_shifts.index') }}" class="btn btn-primary">
                Перейти до чергувань
            </a>
        </div>
    </div>
@endsection
