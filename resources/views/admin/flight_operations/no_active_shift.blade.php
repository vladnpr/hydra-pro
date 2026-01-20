@extends('adminlte::page')

@section('title', 'Активна зміна не знайдена')

@section('content_header')
    <h1>Бойові вильоти</h1>
@endsection

@section('content')
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-ban"></i> Помилка!</h5>
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card card-warning mt-5">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Увага</h3>
                </div>
                <div class="card-body text-center">
                    <h4>У вас немає активної бойової зміни.</h4>
                    <p class="mt-3">Для того, щоб фіксувати бойові вильоти, необхідно спочатку розпочати нове чергування.</p>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('combat_shifts.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Розпочати нове чергування
                    </a>
                    <a href="{{ route('combat_shifts.index') }}" class="btn btn-default ml-2">
                        Переглянути всі чергування
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
