# Desain Landing Page & Dual-Hero Section — CargoTrace
**Inspirasi Visual:** Winzy (Cinematic Layered Typography) $\times$ Veluno (Editorial Split Cutout) $\times$ Sentral Cargo (Logistics Utility)  
**Gaya Utama:** Glassmorphism UI • Modern Bento Grid • Smooth Micro-Animations  
**Tech Stack:** Vue 3 + Inertia.js + Laravel 12 + Tailwind CSS v4

---

## 1. Bedah Konsep: Menggabungkan "Winzy" & "Veluno" ke dalam CargoTrace

Dari kedua gambar referensi yang Anda berikan, masing-masing memiliki kekuatan visual luar biasa yang bisa kita padukan menjadi **Dual-Stage Hero Experience** (atau **Interactive Mode-Switcher Hero**):

### A. Gaya Hero 1 — "Winzy Style" (Full-Bleed Cinematic & Depth Typography)
* **Ciri Khas di Gambar Winzy:**
  - *Floating Pill Navbar* transparan di bagian tengah atas (`backdrop-blur-md bg-white/10 border border-white/20 rounded-full`).
  - Tipografi raksasa (**"CARGOTRACE"** atau **"GLOBAL"**) yang berada **di belakang objek utama** (*depth/parallax layering* antara background langit/laut, teks raksasa semi-transparan, dan siluet kapal kontainer/pesawat kargo di depannya).
  - *Bottom-Left Metrics & Pill CTA*: Angka statistik besar (`20K+`, `300+`, `1000+`) di kiri bawah dengan tombol putih bulat + panah diagonal (`↗`).
  - *Bottom-Right Glass Preview Card*: Kartu kaca (`01/10` slider indikator) yang menampilkan *preview* rute/armada aktif.

### B. Gaya Hero 2 — "Veluno Style" (Clean Editorial Split & Rotating Stamp)
* **Ciri Khas di Gambar Veluno:**
  - Tata letak *Clean Asymmetric Split* di atas latar terang (`bg-slate-50` / `white`) yang memberikan kontras bersih setelah Hero gelap.
  - **Sisi Kiri:** Headline besar nan tegas (*"Precision Logistics Meets Global Visibility"*), tombol kapsul gelap dengan ikon panah lingkaran di ujung kanan, serta *Mini Feature Card* di kiri bawah.
  - **Tengah:** **Rotating Circular Text Badge** (teks melingkar yang berputar perlahan `animate-spin-slow`: *"REAL-TIME TRACKING • GLOBAL VISIBILITY •"* dengan tombol panah `↗` di tengahnya).
  - **Sisi Kanan:** Visual *Container/Map Showcase* besar dengan sudut membulat asimetris (*custom notched/cutout corner*) dan indikator *slide bar* di bawahnya.

---

## 2. Cara Menggabungkan Kedua Hero Tersebut di Landing Page Anda

Ada **2 opsi terbaik** yang sangat keren untuk diterapkan di `Welcome.vue`:

1. **Opsi A — Sequential Dual-Hero (Atas-Bawah Menyatu):**
   - **Bagian Paling Atas (Viewport Pertama):** Menggunakan **Winzy Dark Cinematic Glass Hero** (menampilkan kemegahan *Global Freight* dengan teks raksasa `"CARGO"` berlapis, statistik *live database*, dan *Glassmorphism Floating Navbar*).
   - **Tepat di Bawahnya (Transisi Overlap):** Menggunakan **Veluno Split Editorial Showcase + Utility Cek Resi Sentral Cargo**, lengkap dengan *Rotating Circular Badge*, *Cutout Image Frame*, dan 3-Tab Tracking/Route Utility.
2. **Opsi B — Interactive Hero Switcher (Toggle di Atas Hero):**
   - Pengunjung (dan dosen penguji!) bisa mengklik tombol *switcher* kaca di pojok Hero: **`[01 Cinematic Mode (Winzy)]`** vs **`[02 Editorial Mode (Veluno)]`** dengan animasi transisi `<Transition mode="out-in">` Vue 3 yang super mulus! *(Kami sangat merekomendasikan menggabungkan keduanya atau menyediakan switcher ini agar portofolio Anda terlihat sangat impresif di mata dosen & recruiter).*

---

## 3. Spesifikasi Glassmorphism, Bento Grid, & Animasi

### 3.1 Elemen Glassmorphism (Tailwind CSS v4)
Gunakan resep kelas Tailwind berikut secara konsisten:
- **Dark Glass (Di atas Hero Winzy & Peta):**
  `bg-slate-900/40 backdrop-blur-xl border border-white/15 shadow-[0_8px_32px_0_rgba(0,0,0,0.36)]`
- **Light Frosted Glass (Di atas Seksi Terang / Veluno & Bento):**
  `bg-white/70 backdrop-blur-lg border border-white/60 shadow-xl shadow-slate-900/5`
- **Interactive Glass Pill Button:**
  `bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/25 transition-all duration-300`

### 3.2 Layout Bento Grid (Untuk Fitur CargoTrace & Layanan Sentral Cargo)
Alih-alih membuat kartu 3 kolom biasa yang membosankan, kita gunakan **Asymmetric Bento Grid (`grid-cols-1 md:grid-cols-12 gap-6`)**:
- **Bento Box 1 (`md:col-span-8`, Besar):** *Interactive Live Tracking & Route Preview* — menampilkan simulasi peta Leaflet mini / garis rute *Southeast Asia $\rightarrow$ Europe* dengan titik *pulse* animasi.
- **Bento Box 2 (`md:col-span-4`, Vertikal):** *Deterministic ETA & Delay Engine* — menampilkan visualisasi *threshold* keterlambatan (`LOW`, `MEDIUM`, `HIGH`) secara *real-time*.
- **Bento Box 3 (`md:col-span-4`):** *3 Moda Ekspedisi (Air, Sea, Land)* terinspirasi Sentral Cargo dengan *badge* jumlah *Carrier* aktif dari database.
- **Bento Box 4 (`md:col-span-4`):** *Weather & Geocoding Context* — kartu bergaya *glass* yang menampilkan contoh suhu/cuaca pelabuhan terkini (Open-Meteo).
- **Bento Box 5 (`md:col-span-4`):** *Proactive Exception Management* — menampilkan status penanganan insiden (`CUSTOMS_HOLD`, `WEATHER_DELAY`, dll.).

### 3.3 Efek Animasi Modern (Micro-Interactions & Motion)
1. **Layered Parallax & Text Reveal (Hero Winzy):** Teks raksasa `"CARGOTRACE"` muncul perlahan (`translate-y` + `opacity`) dan bergerak sedikit saat kursor digeser.
2. **Rotating Circular Stamp (Gaya Veluno):** Teks melingkar SVG menggunakan `animate-[spin_14s_linear_infinite]` dengan tombol tengah yang membesar (`scale-110`) saat di-*hover*.
3. **Animated Number Counter:** Angka statistik (`Total Shipments`, `Active Carriers`, `Global Ports`) menghitung naik dari $0$ saat halaman dibuka.
4. **Bento Card Spotlight & Hover Lift:** Kartu Bento memiliki efek `hover:-translate-y-1.5 hover:shadow-2xl transition-all duration-500` serta *glowing border* halus.
5. **Smooth Tab & Result Transition:** Saat user mengetik nomor resi di kotak *Cek Resi* dan menekan *Lacak*, kartu hasil *timeline* muncul dengan animasi *slide-down fade* yang halus.

---

## 4. Kombinasi Warna (Harmoni Winzy, Veluno & CargoTrace)

| Elemen | Warna Tailwind | Nuansa Visual |
| :--- | :--- | :--- |
| **Winzy Hero Background** | `slate-950` $\rightarrow$ `blue-950` + aksen `amber-500/20` | Sinematik, dramatis, memberi efek kedalaman kaca (*glassmorphism* sangat menonjol di latar gelap/kontras). |
| **Veluno Section Background** | `slate-50` & `white` (`#F8FAFC`) | Bersih, mewah, *high-contrast typography* (`text-slate-900`). |
| **Primary Accent** | `blue-600` & `cyan-400` | Mencerminkan teknologi logistik global, laut, dan udara. |
| **Warm Highlight** | `amber-500` / `orange-500` | Mengambil nuansa hangat piramida Winzy & jarum merah/cokelat Veluno untuk badge status penting. |

---

## 5. Prompt Siap Pakai untuk Agent Antigravity (Dual-Hero Winzy & Veluno + Glassmorphism + Bento Grid)

Salin prompt di bawah ini dan kirimkan ke **Agent Antigravity** untuk langsung membangun Landing Page ini di atas prototipe VILT (`Cargotrace_system`) Anda:

```text
Halo Agent! Saya ingin membuat halaman **Landing Page publik (`/` -> `resources/js/Pages/Welcome.vue`)** untuk proyek **CargoTrace** kita (Vue 3 + Inertia.js + Laravel 12 + Tailwind CSS v4).

Saya ingin menggabungkan **fungsionalitas logistik Sentral Cargo** dengan **2 referensi desain Hero modern (Winzy & Veluno)**, ditambah gaya **Glassmorphism**, **Bento Grid**, dan **Animasi Interaktif**.

Berikut spesifikasi lengkap yang harus kamu bangun:

### 1. DUAL-HERO EXPERIENCE (Gabungan Gaya "Winzy" & "Veluno")
Kita akan menampilkan kedua gaya Hero ini secara harmonis (atau dilengkapi *Interactive Hero Mode Switcher* + transisi sekuensial):

- **Hero Stage 1 — Gaya "Winzy" (Cinematic Layered Typography & Glassmorphism):**
  - **Floating Glass Pill Navbar:** Di bagian atas tengah menggunakan `backdrop-blur-xl bg-white/10 border border-white/20 rounded-full` berisi menu navigasi dan tombol `Log in` / `Dashboard` berbentuk pil putih.
  - **Giant Layered Background Text:** Teks raksasa **`CARGOTRACE`** (atau **`LOGISTICS`**) dengan ukuran sangat besar (`text-[12vw] font-black tracking-tighter text-white/15 select-none`) yang berada di lapisan belakang visual kargo/rute global.
  - **Bottom-Left Live Stats & Pill CTA:** Menampilkan 3 metrik nyata dari database (`Total Shipments`, `Active Carriers`, `Global Ports/Hubs`) dengan *animated counter*, deskripsi singkat, dan tombol putih bulat `Track Shipment` + ikon lingkaran panah diagonal (`↗`) persis seperti desain Winzy.
  - **Bottom-Right Glass Slider Card (`01/03`):** Kartu kaca (*glassmorphism*) yang menampilkan *preview* rute/moda pengiriman populer dengan indikator *progress bar* `01/03`.

- **Hero Stage 2 — Gaya "Veluno" (Clean Editorial Split + Rotating Circular Stamp):**
  - **Left Column:** Tipografi editorial besar nan tajam (*"Timeless Precision Meets Global Visibility"*), sub-deskripsi, tombol kapsul gelap dengan ikon panah lingkaran, serta *Mini Feature Card* di kiri bawah.
  - **Centerpiece — Rotating Circular Badge:** Badge SVG teks melingkar yang berputar halus (`animate-[spin_12s_linear_infinite]`) bertuliskan `"REAL-TIME SHIPMENT TRACKING • GLOBAL VISIBILITY •"` dengan lingkaran hitam dan panah `↗` di tengahnya.
  - **Right Column — Cutout Showcase & Multi-Tab Tracking Utility:** Kartu visual dengan lekukan sudut (*custom rounded/cutout style* ala Veluno) yang memuat **3-Tab Utility Bar ala Sentral Cargo**:
    - **Tab 1 (Cek Resi Instan):** Input `shipment_number` / `reference_number` yang langsung menampilkan *stepper* status (`CREATED` s/d `DELIVERED`), lokasi terkini, status *Delay/On-Time*, dan 3 *tracking events* terakhir.
    - **Tab 2 (Cek Rute & Estimasi):** Pilih *Origin*, *Destination*, dan *Moda (`AIR`, `SEA`, `LAND`)*.
    - **Tab 3 (Eksplorasi Lokasi Hub/Port):** Daftar pelabuhan/bandara aktif beserta koordinatnya.

### 2. BENTO GRID SECTION (Untuk Fitur & Layanan)
Buat seksi fitur dan layanan ekspedisi (Udara, Laut, Darat) menggunakan **Asymmetric Bento Grid (`grid-cols-1 md:grid-cols-12 gap-6`)** dengan efek **Glassmorphism & Hover Glow**:
- Kotak Bento 1 (`col-span-8`): Visualisasi *Live Route & Timeline Visibility*.
- Kotak Bento 2 (`col-span-4`): *Deterministic ETA & Delay Severity Rule* (`LOW`, `MEDIUM`, `HIGH`).
- Kotak Bento 3 (`col-span-4`): *Ekspedisi Udara, Laut, & Darat* beserta daftar mitra *Carrier* dari database.
- Kotak Bento 4 (`col-span-4`): *Weather & Geocoding Context Integration*.
- Kotak Bento 5 (`col-span-4`): *Exception Management Workflow* (`OPEN` -> `INVESTIGATING` -> `RESOLVED`).

### 3. ANIMASI & INTERAKTIVITAS
- Tambahkan animasi halus menggunakan CSS/Vue Transition:
  - *Fade-up & scale* saat elemen masuk layar.
  - *Continuous slow spin* pada *Circular Stamp* ala Veluno.
  - *Hover lift* (`hover:-translate-y-1.5`), *glass reflection*, dan *pulse indicator* pada status pengiriman.
  - *Number counter animation* pada statistik database.

### 4. BACKEND & ROUTING (`routes/web.php`)
- Ubah route `/` agar menampilkan halaman `Welcome.vue` (tidak langsung redirect paksa ke `/login`), sambil mengirimkan data statistik nyata (`shipments_count`, `carriers`, `locations`) dan mendukung query pencarian resi publik (`?tracking_number=...`).
- Pastikan semua automated test yang sudah ada tetap **PASS** (sesuaikan jika ada test yang mengecek route `/`).

Sesuai aturan kolaborasi kita:
1. Paparkan dulu rencana perubahan route/controller dan struktur komponen di `Welcome.vue`.
2. Tanyakan jika ada hal yang perlu diklarifikasi sebelum kamu menulis kodenya!
```