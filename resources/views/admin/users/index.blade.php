@extends('adminlte::page')

@section('title', 'Користувачі')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Управління користувачами</h1>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Додати користувача
        </a>
    </div>
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-check"></i> Успіх!</h5>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-ban"></i> Помилка!</h5>
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ім'я</th>
                        <th class="d-none d-md-table-cell">Email</th>
                        <th>Роль</th>
                        <th class="d-none d-lg-table-cell">Створено</th>
                        <th style="width: 100px">Дії</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td class="d-none d-md-table-cell">{{ $user->email }}</td>
                            <td>
                                @php
                                    $roleBadge = match($user->role) {
                                        'admin' => 'danger',
                                        'user' => 'primary',
                                        'guest' => 'secondary',
                                        default => 'info'
                                    };
                                    $shortRole = match($user->role) {
                                        'admin' => 'adm',
                                        'user' => 'usr',
                                        'guest' => 'gst',
                                        default => $user->role
                                    };
                                @endphp
                                <span class="badge badge-{{ $roleBadge }} d-none d-md-inline">{{ $user->role }}</span>
                                <span class="badge badge-{{ $roleBadge }} d-inline d-md-none">{{ $shortRole }}</span>
                            </td>
                            <td class="d-none d-lg-table-cell">{{ $user->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Ви впевнені?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
