@extends('adminlte::page')

@section('title', 'Додати новий дрон')

@section('content_header')
    <h1>Додати новий дрон</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Основна інформація</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form action="{{ route('drones.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Назва</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" placeholder="Введіть назву дрона" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="model">Модель</label>
                            <input type="text" name="model" class="form-control @error('model') is-invalid @enderror" id="model" placeholder="Введіть модель" value="{{ old('model') }}" required>
                            @error('model')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="status">Статус</label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="1" {{ old('status') == 'true' ? 'selected' : '' }}>Активний</option>
                                <option value="0" {{ old('status') == 'false' ? 'selected' : '' }}>Неактивний</option>
                            </select>
                            @error('status')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Зберегти</button>
                        <a href="{{ route('drones.index') }}" class="btn btn-default float-right">Скасувати</a>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
    </div>
@endsection
