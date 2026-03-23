@extends('adminlte::page')

@section('title', 'Додати боєприпас ППО')

@section('content_header')
    <h1>Додати боєприпас ППО</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <form action="{{ route('air-defence.ammunition.store') }}" method="POST">
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
                            <label for="type">Тип</label>
                            <input type="text" name="type" id="type" class="form-control @error('type') is-invalid @enderror" value="{{ old('type') }}">
                            @error('type')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="status">Статус</label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Активний</option>
                                <option value="spent" {{ old('status') == 'spent' ? 'selected' : '' }}>Витрачено</option>
                            </select>
                            @error('status')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('air-defence.ammunition.index') }}" class="btn btn-default">Скасувати</a>
                        <button type="submit" class="btn btn-primary float-right">Зберегти</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
