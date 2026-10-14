<?php

namespace App\Http\Controllers;

// Import model yang digunakan untuk berinteraksi dengan database
use App\Models\order; //Model untuk tabel orders (data pesanan utama)
use App\Models\event; //model untuk tabel events (data konser)
use App\Models\order_detail; //Model untuk tabel order_details (rincian ticket dalam pesanan)
use Illuminate\Support\Facades\DB; //Facades DB untuk mengelola transaksi database (commit/rollback)
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $event = Event::all(); //Mengambil semua data konser dari tabel 'events'
        $events = $event; //variabel alias $event untuk kompabilitas jika ada View yang menggunakannya
        return view('customer.index', compact('event', 'events')); //Mengirimkan data makanan ke view 'resource/views/customer/index.blade.php
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255', //Nama pemesan wajib diisi teks maks 255 karakter
            'customer_email' => 'required|string|max:255', //Nama pemesan wajib diisi teks maks 255 karakter
            'items' => 'required|array', //Data item harus berupa array [food_id => quantity]
            'items.*' => 'nullable|integer|min:0', //Setiap jumlah item bernilai angka>=0
        ]);

        // Filter ticket yang dipesan: Hanya ambil item dengan sejumlah porsi(quantity) lebih dari 0
        $orderedItems = array_filter($request->items, fn($qty) => $qty > 0);

        //Jika pemesan tidak memilih tiket apapun (semua pilihan 0), kembalikan dengab pesan error
        if(empty($orderedItems)){
            return back()->with('error', 'Pilih Minimal satu tiket konser!');
        }

        //Memulai database Transaction
        //Agar jika salah satu proses insert gagal, seluruh data yang sudah masuk akan dibatalkan (rollback)
        DB::beginTransaction();
        try{
            // Membuat record pesanan baru di tabel 'orders'
            $order = Order::create([
                'customer_name' => $request->customer_name, //Nama Pemesan
                'customer_email' => $request->customer_email, //Email Pemesan
                'total_price' => 0, //Diinisialisasi 0, akan dihitung setelah looping rincian
                'status' => 'Pending', // Status awal pesanan: 'Pending' (Sesuai ENUM database: yang di set pending sebagai default status)
            ]);

            $totalPrice = 0; //Variabel penampung akumulasi total harga

            //looping setiap ticket yang dipesan untuk menghitung subtotal dan menyimpan ke 'order_details'
            foreach ($orderedItems as $eventId => $quantity){
                // Cari data konser berdasarkan ID, Jika tidak ditemukan akan menghasilkan 404
                $event = Event::findOrFail($eventId);

                //Hitung subtotal: harga ticket dikali jumlah pesanan
                $subtotal = $event->ticket_price * $quantity;
                $totalPrice += $subtotal; // Tambahkan ke total akumulasi

                //Simpan rincian item ke tabel 'order_details'
                order_detail::create([
                    'order_id' => $order->id, //ID pesanan utama sebagai foreign key
                    'event_id' => $event->id, //ID menu makanan
                    'quantity' => $quantity, //Jumlah porsi yang dipesan
                    'subtotal' => $subtotal, //Total harga untuk item ini
                ]);
            }

            //Update total harga akhir pada pesanan utama
            $order->update(['total_price' => $totalPrice]);

            //Jika semua query berhasil tanpa error, Simpan ke database
            DB::commit();

            //redirect kembali ke halaman ticket box dengan notifikasi sukses
            return redirect()->route('customer.index')->with('success', 'Pesanan Berhasil dibuat! Dengan email:' .$order->customer_email);
        }
        catch(\Exception $e) {
            //Jika terjadi kesalahan query/sistem, batalkan seluruh perubahan database
            DB::rollBack();

            //Kembalikan ke halaman form dengan pesan error detail
            return back()->with('error', 'Gagal memproses pesanan: ' . $e->getMessage());
        }
    }

    public function adminDashboard()
    {
        //Mengambil semua data pesanan dari database
        $order = Order::with('order_details.events')->latest()->get();

        return view ('dashboard', compact('order'));
    }

    //Mmeperbarui status pesanan

    public function updateStatus(Request $request, $id)
    {
        //validasi input status: wajib diisi dan nilainya harus salah satu dari ENUM database
        $request->validate([
            'status'=>'required|string|in:Pending,Confirmed,Cancelled'
        ]);

        //Cari pesanan berdasarkan ID yang dikirim melalui parameter URL
        $order = Order::findOrFail($id);
        //Perbarui kolom 'status' pada data pesanan
        $order->update(['status' => $request->status]);

        //kembalikan ke halaman sebelumnya dengan pesan sukses
        return back()->with('success', 'Status pesanan #' . $order->id . 'Berhasil diubah menjadi' . $order->status . '!');
    }

    /**
     * Display the specified resource.
     */
    public function show(order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(order $order)
    {
        //
    }
}
