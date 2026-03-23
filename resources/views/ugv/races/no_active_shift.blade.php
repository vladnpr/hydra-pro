@extends('adminlte::page')

@section('title', 'Рейси НРК')

@section('content_header')
    <h1>Рейси НРК</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-body text-center">
            <h4>У вас немає активного чергування UGV</h4>
            <p>Для роботи з рейсами спочатку розпочніть або приєднайтеся до чергування.</p>
            <a href="{{ route('ugv.combat_shifts.index') }}" class="btn btn-primary">
                Перейти до чергувань
            </a>
        </div>
    </div>
@endsection
