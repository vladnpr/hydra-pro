@extends('adminlte::page')

@section('title', 'Редагувати боєприпас')

@section('content_header')
    <h1>Редагувати боєприпас</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Редагування: {{ $ammunition->name }}</h3>
                </div>
                <form action="{{ route('vampire.ammunition.update', $ammunition->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="type" value="vampire">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Назва</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" placeholder="Введіть назву" value="{{ old('name', $ammunition->name) }}" required>
                            @error('name')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="status">Статус</label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="1" {{ old('status', $ammunition->status) ? 'selected' : '' }}>Активний</option>
                                <option value="0" {{ !old('status', $ammunition->status) ? 'selected' : '' }}>Неактивний</option>
                            </select>
                            @error('status')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info">Оновити</button>
                        <a href="{{ route('vampire.ammunition.index') }}" class="btn btn-default float-right">Скасувати</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
