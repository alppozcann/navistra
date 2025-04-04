<?php

namespace App\Http\Controllers;

use App\Models\Yuk;
use App\Models\GemiRoute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class YukController extends Controller
{
    /**
     * Tüm yükleri listele.
     */
    public function index()
    {
        $yukler = Yuk::with('user')->where('status', 'active')->latest()->get();
        return view('yukler.index', compact('yukler'));
    }

    /**
     * Yeni yük eklemek için form göster.
     */
    public function create()
    {
        // Sadece yük veren tipi kullanıcılar yük ekleyebilir
        if (!Auth::user()->isYukVeren()) {
            return redirect()->route('profile.edit')
                ->with('error', 'Yük eklemek için yük veren profilinizi tamamlamanız gerekiyor.');
        }

        return view('yukler.create');
    }

    /**
     * Yeni eklenen yükü kaydet.
     */
    public function store(Request $request)
    {
        // Sadece yük veren tipi kullanıcılar yük ekleyebilir
        if (!Auth::user()->isYukVeren()) {
            return redirect()->route('yukler.index')
                ->with('error', 'Yük eklemek için yük veren profilinizi tamamlamanız gerekiyor.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'yuk_type' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'from_location' => 'required|string|max:255',
            'to_location' => 'required|string|max:255',
            'proposed_price' => 'required|numeric|min:0',
            'desired_delivery_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        // Boyutları JSON olarak formatlama
        $dimensions = null;
        if ($request->width !== null && $request->length !== null && $request->height !== null) {
            $dimensions = [
                'width' => $request->width,
                'length' => $request->length,
                'height' => $request->height,
            ];
        }

        $yuk = Yuk::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'yuk_type' => $request->yuk_type,
            'weight' => $request->weight,
            'dimensions' => $dimensions,
            'from_location' => $request->from_location,
            'to_location' => $request->to_location,
            'proposed_price' => $request->proposed_price,
            'desired_delivery_date' => $request->desired_delivery_date,
            'description' => $request->description,
            'status' => 'active',
        ]);

        return redirect()->route('yukler.show', $yuk)
            ->with('success', 'Yük ilanı başarıyla eklendi.');
    }

    /**
     * Belirli bir yükü göster.
     */
    public function show(Yuk $yuk)
    {
        // Eşleşebilecek gemi rotalarını bul
        $matchingRoutes = GemiRoute::where('status', 'active')
            ->where('start_location', $yuk->from_location)
            ->where('end_location', $yuk->to_location)
            ->where('available_capacity', '>=', $yuk->weight)
            ->where('departure_date', '<=', $yuk->desired_delivery_date)
            ->get();
        
        return view('yukler.show', compact('yuk', 'matchingRoutes'));
    }

    /**
     * Yük düzenleme formunu göster.
     */
    public function edit(Yuk $yuk)
    {
        // Sadece ilan sahibi düzenleyebilir
        if (Auth::id() !== $yuk->user_id) {
            return redirect()->route('yukler.index')
                ->with('error', 'Bu yükü düzenleme yetkiniz yok.');
        }

        return view('yukler.edit', compact('yuk'));
    }

    /**
     * Yükü güncelle.
     */
    public function update(Request $request, Yuk $yuk)
    {
        // Sadece ilan sahibi güncelleyebilir
        if (Auth::id() !== $yuk->user_id) {
            return redirect()->route('yukler.index')
                ->with('error', 'Bu yükü güncelleme yetkiniz yok.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'yuk_type' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'from_location' => 'required|string|max:255',
            'to_location' => 'required|string|max:255',
            'proposed_price' => 'required|numeric|min:0',
            'desired_delivery_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        // Boyutları JSON olarak formatlama
        $dimensions = null;
        if ($request->width !== null && $request->length !== null && $request->height !== null) {
            $dimensions = [
                'width' => $request->width,
                'length' => $request->length,
                'height' => $request->height,
            ];
        }

        $yuk->update([
            'title' => $request->title,
            'yuk_type' => $request->yuk_type,
            'weight' => $request->weight,
            'dimensions' => $dimensions,
            'from_location' => $request->from_location,
            'to_location' => $request->to_location,
            'proposed_price' => $request->proposed_price,
            'desired_delivery_date' => $request->desired_delivery_date,
            'description' => $request->description,
        ]);

        return redirect()->route('yukler.show', $yuk)
            ->with('success', 'Yük ilanı başarıyla güncellendi.');
    }

    /**
     * Yükü sil.
     */
    public function destroy(Yuk $yuk)
    {
        // Sadece ilan sahibi silebilir
        if (Auth::id() !== $yuk->user_id) {
            return redirect()->route('yukler.index')
                ->with('error', 'Bu yükü silme yetkiniz yok.');
        }

        $yuk->delete();

        return redirect()->route('yukler.index')
            ->with('success', 'Yük ilanı başarıyla silindi.');
    }

    /**
     * Yükü bir gemi rotasına eşleştir.
     */
    public function requestMatch(Yuk $yuk, GemiRoute $gemiRoute)
    {
        // Sadece yük sahibi eşleştirme talep edebilir
        if (Auth::id() !== $yuk->user_id) {
            return redirect()->route('yukler.show', $yuk)
                ->with('error', 'Bu yük için eşleştirme talep etme yetkiniz yok.');
        }

        // Yükün durumunu kontrol et
        if ($yuk->status !== 'active') {
            return redirect()->route('yukler.show', $yuk)
                ->with('error', 'Bu yük zaten eşleştirilmiş veya aktif değil.');
        }

        // Kapasite kontrolü
        if ($yuk->weight > $gemiRoute->available_capacity) {
            return redirect()->route('yukler.show', $yuk)
                ->with('error', 'Seçtiğiniz gemi rotasında yeterli kapasite yok.');
        }

        // Yükü eşleştir
        $yuk->update([
            'status' => 'matched',
            'matched_gemi_route_id' => $gemiRoute->id,
        ]);

        // Gemi rotasının kapasitesini güncelle
        $gemiRoute->update([
            'available_capacity' => $gemiRoute->available_capacity - $yuk->weight,
        ]);

        return redirect()->route('yukler.show', $yuk)
            ->with('success', 'Yük başarıyla bu rotaya eşleştirildi.');
    }
    
    /**
     * Eşleştirmeyi iptal et.
     */
    public function cancelMatch(Yuk $yuk)
    {
        // Sadece yük sahibi veya rota sahibi iptal edebilir
        $gemiRoute = $yuk->matchedGemiRoute;
        
        if (!$gemiRoute || (Auth::id() !== $yuk->user_id && Auth::id() !== $gemiRoute->user_id)) {
            return redirect()->route('yukler.show', $yuk)
                ->with('error', 'Bu eşleştirmeyi iptal etme yetkiniz yok.');
        }

        // Eşleştirmenin durumunu kontrol et
        if ($yuk->status !== 'matched') {
            return redirect()->route('yukler.show', $yuk)
                ->with('error', 'Bu yük zaten eşleştirilmemiş.');
        }

        // Gemi rotasının kapasitesini geri yükle
        $gemiRoute->update([
            'available_capacity' => $gemiRoute->available_capacity + $yuk->weight,
        ]);

        // Eşleştirmeyi kaldır
        $yuk->update([
            'status' => 'active',
            'matched_gemi_route_id' => null,
        ]);

        return redirect()->route('yukler.show', $yuk)
            ->with('success', 'Eşleştirme başarıyla iptal edildi.');
    }
    
    /**
     * Teslimati tamamla.
     */
    public function completeDelivery(Yuk $yuk)
    {
        // Sadece yük sahibi tamamlayabilir
        if (Auth::id() !== $yuk->user_id) {
            return redirect()->route('yukler.show', $yuk)
                ->with('error', 'Bu teslimatı tamamlama yetkiniz yok.');
        }

        // Eşleştirmenin durumunu kontrol et
        if ($yuk->status !== 'matched') {
            return redirect()->route('yukler.show', $yuk)
                ->with('error', 'Bu yük henüz eşleştirilmemiş.');
        }

        // Teslimatı tamamla
        $yuk->update([
            'status' => 'completed',
        ]);

        return redirect()->route('yukler.show', $yuk)
            ->with('success', 'Teslimat başarıyla tamamlandı.');
    }
}
