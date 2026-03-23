@extends('adminlte::page')

@section('title', 'Додати дрон ППО')

@section('content_header')
    <h1>Додати дрон ППО</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <form action="{{ route('air-defence.drones.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Назва</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="model">Модель</label>
                            <input type="text" name="model" id="model" class="form-control @error('model') is-invalid @enderror" value="{{ old('model') }}">
                            @error('model')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="status">Статус</label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Активний</option>
                                <option value="lost" {{ old('status') == 'lost' ? 'selected' : '' }}>Втрачено</option>
                                <option value="repair" {{ old('status') == 'repair' ? 'selected' : '' }}>В ремонті</option>
                            </select>
                            @error('status')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('air-defence.drones.index') }}" class="btn btn-default">Скасувати</a>
                        <button type="submit" class="btn btn-primary float-right">Зберегти</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
