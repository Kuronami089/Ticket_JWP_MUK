<?php

namespace App\Http\Controllers;

use App\Models\event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */

        // Menampilkan daftar konser untuk admin
    public function index()
    {
        $event = Event::latest()->paginate(10);
        return view('admin.events.index', compact('event'));
    }

    /**
     * Show the form for creating a new resource.
     */

    //menampilkan for tambah konser
    public function create()
    {
        return view('admin.events.create');
    }

    /**
     * Store a newly created resource in storage.
     */

    //simpan menu baru ke database
    public function store(Request $request)
    {
        //validasi data input
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:Musik,Festival,Seminar',
            'ticket_price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        //upload gambar jika ada
        $imagePath = null;
        if ($request->hasFile('image')){
            $imagePath = $request->file('image')->store('events', 'public');
        }

        //Simpan data konser
        Event::create([
            'title' => $request->title,
            'category' => $request->category,
            'ticket_price' =>$request->ticket_price,
            'description' => $request->description,
            'image' => $imagePath,
        ]);

       return redirect()->route('events.index')->with('success', 'Data Konser berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(event $event)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */

    //menampilkan form edit menu
    public function edit(event $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    /**
     * Update the specified resource in storage.
     */

    //menyimpan perubahan data ke database
    public function update(Request $request, event $event)
    {
        //validasi data input
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:Musik,Festival,Seminar',
            'ticket_price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $imagePath = $event->image;

        //Jika upload gambar baru, menghapus gambar lama
        if($request->hasFile('image')){
            if ($event->image && Storage::disk('public')->exists($event->image)){  //Check apakah ada gambar di dalam data tersebut jika ada maka akan di hapus
                Storage::disk('public')->delete($event->image);
            }
            $imagePath = $request->file('image')->store('events', 'public');
        }

        //Perbarui data konser
        $event->update([
            'title' => $request->title,
            'category' => $request->category,
            'ticket_price' =>$request->ticket_price,
            'description' => $request->description,
            'image' => $imagePath,
        ]);

        return redirect()->route('events.index')->with('success', 'Data Konser berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    //Hapus data beserta file gambarnya
    public function destroy(event $event)
    {
        //Hapus file gambar jika ada
        if ($event->image && Storage::disk('public')->exists($event->image)){
            Storage::disk('public')->delete($event->image);
    }

        //Hapus data dari database
        $event->delete();
        return redirect()->route('events.index')->with('success', 'Data Konser berhasil diperbarui!');
    }
}