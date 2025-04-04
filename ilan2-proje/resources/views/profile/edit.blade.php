@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h4>Profil Bilgileri</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Ad Soyad</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">E-posta</label>
                        <input type="email" class="form-control" id="email" value="{{ $user->email }}" readonly disabled>
                        <div class="form-text">E-posta adresiniz değiştirilemez.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Hakkınızda</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $user->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            @if($user->isGemici())
                                Gemicilik deneyiminiz, gemi bilgileriniz gibi detayları ekleyebilirsiniz.
                            @elseif($user->isYukVeren())
                                Şirketiniz, taşıttığınız yükler hakkında bilgi verebilirsiniz.
                            @else
                                Kendiniz hakkında bilgi ekleyin.
                            @endif
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Telefon Numaranız</label>
                        <input type="numeric" class="form-control @error('phone_number') is-invalid @enderror" id="phone_number" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" required>
                        @error('phone_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>


                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Profili Güncelle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h4>Kullanıcı Tipi</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('profile.update_type') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label">Kullanıcı Tipinizi Seçin</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3 {{ $user->user_type === 'gemici' ? 'border-primary' : '' }}">
                                    <div class="card-body">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="user_type" id="gemiciType" value="gemici" {{ $user->user_type === 'gemici' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="gemiciType">
                                                <h5>Gemici</h5>
                                                <p class="text-muted">Kargo taşımacılığı yapıyorum, yük taşımak istiyorum.</p>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3 {{ $user->user_type === 'yuk_veren' ? 'border-primary' : '' }}">
                                    <div class="card-body">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="user_type" id="yukVerenType" value="yuk_veren" {{ $user->user_type === 'yuk_veren' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="yukVerenType">
                                                <h5>Yük Veren</h5>
                                                <p class="text-muted">Yüküm var, taşınmasını istiyorum.</p>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Kullanıcı Tipini Güncelle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4>Profil İşlemleri</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    @if($user->isGemici())
                        <div class="col-md-6">
                            <div class="d-grid gap-2">
                                <a href="{{ route('profile.gemi_routes') }}" class="btn btn-outline-primary">Rotalarımı Görüntüle</a>
                                <a href="{{ route('gemi_routes.create') }}" class="btn btn-outline-success">Yeni Rota Ekle</a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-grid gap-2">
                                <a href="{{ route('yukler.index') }}" class="btn btn-outline-info">Mevcut Yükleri Görüntüle</a>
                            </div>
                        </div>
                    @elseif($user->isYukVeren())
                        <div class="col-md-6">
                            <div class="d-grid gap-2">
                                <a href="{{ route('profile.yukler') }}" class="btn btn-outline-primary">Yüklerimi Görüntüle</a>
                                <a href="{{ route('yukler.create') }}" class="btn btn-outline-success">Yeni Yük Ekle</a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-grid gap-2">
                                <a href="{{ route('gemi_routes.index') }}" class="btn btn-outline-info">Mevcut Rotaları Görüntüle</a>
                            </div>
                        </div>
                    @else
                        <div class="col-12">
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                Lütfen yukarıdan bir kullanıcı tipi seçin.
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
