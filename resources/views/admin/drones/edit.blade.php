@extends('adminlte::page')

@section('title', 'Редагувати дрон')

@section('content_header')
    <h1>Редагувати дрон</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Інформація про дрон #{{ $drone->id }}</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form action="{{ route('drones.update', $drone->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Назва</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" placeholder="Введіть назву дрона" value="{{ old('name', $drone->name) }}" required>
                            @error('name')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="model">Модель</label>
                            <input type="text" name="model" class="form-control @error('model') is-invalid @enderror" id="model" placeholder="Введіть модель" value="{{ old('model', $drone->model) }}" required>
                            @error('model')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="status">Статус</label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="1" {{ old('status', $drone->status) == 1 ? 'selected' : '' }}>Активний</option>
                                <option value="0" {{ old('status', $drone->status) == 0 ? 'selected' : '' }}>Неактивний</option>
                            </select>
                            @error('status')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer">
                        <button type="submit" class="btn btn-info">Оновити</button>
                        <a href="{{ route('drones.index') }}" class="btn btn-default float-right">Скасувати</a>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
    </div>
@endsection
