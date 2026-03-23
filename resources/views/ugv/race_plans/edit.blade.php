@extends('adminlte::page')

@section('title', 'Редагувати рейс плану')

@section('content_header')
    <h1>Редагувати рейс плану</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Рейс: {{ $plan->position_name }}</h3>
                </div>
                <form action="{{ route('ugv.race_plans.update', $plan->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="position_name">Назва позиції / Рейси</label>
                            <input type="text" name="position_name" id="position_name" class="form-control" value="{{ old('position_name', $plan->position_name) }}" required>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Зберегти зміни</button>
                        <a href="{{ route('ugv.races.index') }}" class="btn btn-default">Скасувати</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
