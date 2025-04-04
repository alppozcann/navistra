@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Admin Dashboard</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Toplam Kullanıcı</h5>
                                <p class="card-text display-4">{{ $users->count() }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Toplam İlan</h5>
                                <p class="card-text display-4">{{ $ilanlar->count() }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Son 24 Saat</h5>
                                <p class="card-text display-4">{{ $ilanlar->where('created_at', '>=', now()->subDay())->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Kullanıcı Listesi</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ad Soyad</th>
                                <th>E-posta</th>
                                <th>Kayıt Tarihi</th>
                                <th>Rol</th>
                                <th>İlanlar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->created_at->format('d.m.Y H:i') }}</td>
                                    <td>
                                        @if($user->is_admin)
                                            <span class="badge bg-danger">Admin</span>
                                        @else
                                            <span class="badge bg-secondary">Kullanıcı</span>
                                        @endif
                                    </td>
                                    <td>{{ $ilanlar->where('user_id', $user->id)->count() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Son İlanlar</h5>
                <a href="{{ route('ilans.index') }}" class="btn btn-sm btn-primary">Tüm İlanlar</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Başlık</th>
                                <th>Fiyat</th>
                                <th>Kullanıcı</th>
                                <th>Tarih</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ilanlar->take(10) as $ilan)
                                <tr>
                                    <td>{{ $ilan->id }}</td>
                                    <td>{{ $ilan->baslik }}</td>
                                    <td>{{ number_format($ilan->fiyat, 2) }} TL</td>
                                    <td>{{ $ilan->user->name ?? 'Bilinmiyor' }}</td>
                                    <td>{{ $ilan->created_at->format('d.m.Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('ilans.show', $ilan) }}" class="btn btn-sm btn-outline-primary">Görüntüle</a>
                                            <a href="{{ route('ilans.edit', $ilan) }}" class="btn btn-sm btn-outline-secondary">Düzenle</a>
                                            <form action="{{ route('ilans.destroy', $ilan) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bu ilanı silmek istediğinizden emin misiniz?')">Sil</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
