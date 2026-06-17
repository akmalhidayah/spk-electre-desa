<footer class="landing-footer">
    <div>
        <strong>{{ $setting->nama_desa ?? 'Desa Barambang' }}</strong>
        <p>{{ $setting->alamat ?? (($setting->kecamatan ?? 'Kecamatan Sinjai Borong').', '.($setting->kabupaten ?? 'Kabupaten Sinjai').', '.($setting->provinsi ?? 'Sulawesi Selatan')) }}</p>
    </div>
    <div class="landing-footer-contact">
        @if ($setting->email)
            <span>{{ $setting->email }}</span>
        @endif
        @if ($setting->telepon)
            <span>{{ $setting->telepon }}</span>
        @endif
        <span>&copy; {{ now()->year }} {{ $setting->nama_desa ?? 'Desa Barambang' }}</span>
    </div>
</footer>
