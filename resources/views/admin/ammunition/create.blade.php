@extends('adminlte::page')

@section('title', 'Додати новий боєприпас')

@section('content_header')
    <h1>Додати новий боєприпас</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Основна інформація</h3>
                </div>
                <form action="{{ route('ammunition.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Назва</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" placeholder="Введіть назву боєприпасу" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="status">Статус</label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="1" {{ old('status') === '1' || old('status') === null ? 'selected' : '' }}>Активний</option>
                                <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Неактивний</option>
                            </select>
                            @error('status')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Зберегти</button>
                        <a href="{{ route('ammunition.index') }}" class="btn btn-default float-right">Скасувати</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
