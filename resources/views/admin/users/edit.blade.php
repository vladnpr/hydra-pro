@extends('adminlte::page')

@section('title', 'Редагувати користувача')

@section('content_header')
    <h1>Редагувати користувача: {{ $user->name }}</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-info">
                <form action="{{ route('users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Ім'я</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="role">Роль</label>
                            <select name="role" id="role" class="form-control @error('role') is-invalid @enderror" required>
                                <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>Користувач</option>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Адміністратор</option>
                                <option value="guest" {{ old('role', $user->role) == 'guest' ? 'selected' : '' }}>Гість</option>
                            </select>
                            @error('role')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">Пароль (залиште порожнім, якщо не хочете змінювати)</label>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Підтвердження пароля</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                        </div>
                    </div>

                    <div class="card-footer">
                        <a href="{{ route('users.index') }}" class="btn btn-default">Скасувати</a>
                        <button type="submit" class="btn btn-info float-right">Оновити</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
