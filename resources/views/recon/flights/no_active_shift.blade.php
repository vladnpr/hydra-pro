@extends('adminlte::page')

@section('title', 'Немає активного чергування')

@section('content_header')
    <h1>Польоти розвідки</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        Потрібно розпочати чергування
                    </h3>
                </div>
                <div class="card-body">
                    <p>Для фіксації польотів розвідки необхідно мати відкрите чергування типу <strong>розвідка</strong>.</p>
                    <p>Будь ласка, розпочніть нове чергування або приєднайтеся до існуючого.</p>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('recon.combat_shifts.index') }}" class="btn btn-primary">
                        <i class="fas fa-shield-alt"></i> Перейти до чергувань розвідки
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
